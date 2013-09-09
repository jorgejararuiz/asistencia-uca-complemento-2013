<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class AdministrationController extends Zend_Controller_Action
{
    private $_debugLogger;
    private $_errorLogger;
    private $_infoLogger;
    
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG); 
    }

    public function indexAction(){
        
        if (Zend_Auth::getInstance()->hasIdentity() == 1) {
            $this->_redirect("/register/signup");       
        }

        
    }
    
    public function loginAction(){
       
        if (Zend_Auth::getInstance()->hasIdentity() == 1) {
            $this->_redirect("/register/signup");       
        }
        
        $username = $this->getRequest()->getParam('username');
        $password = $this->getRequest()->getParam('password');
        
        if (trim($username) == "") {
            $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Authentication error!</div>";
            $this->_debugLogger->debug("Error de Autenticacion");
            $this->_redirect("index");
        } else {
            //Se verifica que sea administrador
            if($this->checkUser($username, md5($password))){
            
                //Si es, se hace la autenticacion para mantener la sesión
                $authentication = new Uca_Auth($username, $password);

                if ($authentication->authenticate()) {
                    $this->_debugLogger->debug("Login Exitoso");
                    $this->_redirect("/register/signup");
                } else {
                    $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Verifique el nombre de usuario y password!</div>";
                    $this->_debugLogger->debug("Verifique nombre de usuario y contraseña");
                    $this->_redirect("/administration/index");
                }
            }else{
                    $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Verifique el nombre de usuario y password!</div>";
                    $this->_debugLogger->debug("Verifique nombre de usuario y contraseña");
                    $this->_redirect("/administration/index");                
            }
        }
        
        
    }
    
    public function logoutAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect("index");
        
    }
    
    private function checkUser($email, $password){
       /*Verifica que el usuario que va a ser login sea administrador 
        * para poder hacer el registro de usuario nuevo
        */
       $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
       ));
       $select = "SELECT * FROM personas AS PERSONAS INNER JOIN rol_x_persona as ROL ".
                 "on ROL.id_rol = 1 ".
                 "and PERSONAS.id_persona = ROL.id_persona ". 
                 "and PERSONAS.email = '". $email .
                 "' and PERSONAS.password = '". $password ."'";
       
       $this->_debugLogger->debug($select);
       try {
           $result = $db->fetchRow($select);
       } catch (Exception $exc) {
           $this->_debugLogger->debug($exc->getTraceAsString());
       }

       
       if($result){
           return true;
       }
       return false;
        
        
    }
    
    public function adminAction(){
        
        
    }
}

class Users extends Zend_Db_Table_Abstract
{

    protected $_name="public.personas";
    
    function checkUnique($email){
       
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $authAdapter = new Zend_Auth_Adapter_DbTable($db);
       $select = $db->select()
                ->from(array('public.personas'), array('email'))
                ->where("email = ?", $email);
       $result = $db->fetchRow($select);
       
       if($result){
           return true;
       }
       return false;
   }
           
   function checkKey($email){
       
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $authAdapter = new Zend_Auth_Adapter_DbTable($db);
       $result = $db->fetchRow("SELECT * FROM public.users_account_reset AS TABLA"
                               . " WHERE TABLA.user_mail ='". $email 
                               . "' and TABLA.timestamp < (select current_timestamp - interval '30' minute)" 
                               . " ORDER BY TABLA.reset_id DESC LIMIT 1"         
               );
       
       if($result){
           return true;
       }
       return false;
   }
    
}


?>

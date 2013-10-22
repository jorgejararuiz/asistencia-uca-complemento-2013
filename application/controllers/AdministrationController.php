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
        //$this->_helper->layout->disableLayout();
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
    
    public function getteachersAction(){
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $config = new Zend_Config_Ini('../application/configs/application.ini', 'production');
        $host = $config->resources->db->params->host;
        $port = $config->resources->db->params->port;
        $dbname = $config->resources->db->params->dbname;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;

        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => $host,  
            'username' => $user,  
            'password' => $pass,  
            'dbname' => $dbname        
        ));
        
        
       $select = "select A.id_persona, A.nombre as nombre, A.apellido, A.email, A.ci, ".
	"C.id_materia, C.nombre as nombre_materia " .
	"from personas as A " .
	"inner join rol_x_persona as B " .
	"on B.id_persona = A.id_persona ".
	"and B.id_rol = 2 ".
	"inner join materias as C ".
	"on C.id_rol_x_persona = B.id_rol_x_persona";
       
       try {
            $result = $db->fetchAll($select);
            $this->_debugLogger->debug(print_r($result, true));  
            $json = array(
                "people" => $result
            );
            
            $this->_debugLogger->debug(print_r(Zend_Json_Encoder::encode($json), true));
            
       } catch (Exception $exc) {
           $this->_debugLogger->debug($exc->getMessage());
       }
       
    }
    
    public function getworkersAction(){
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $config = new Zend_Config_Ini('../application/configs/application.ini', 'production');
        $host = $config->resources->db->params->host;
        $port = $config->resources->db->params->port;
        $dbname = $config->resources->db->params->dbname;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;

        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => $host,  
            'username' => $user,  
            'password' => $pass,  
            'dbname' => $dbname        
        ));
        
        
       $select = "select id_persona, nombre, apellido, email, ci from personas";
       try {
            $result = $db->fetchAll($select);
            $this->_debugLogger->debug(print_r($result, true));  
            $json = array(
                "people" => $result
            );
            
            $this->_debugLogger->debug(print_r(Zend_Json_Encoder::encode($json), true));
            
       } catch (Exception $exc) {
           $this->_debugLogger->debug($exc->getMessage());
       }
       
    }
    
    public function getlistcheckinoutworkersAction(){
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $config = new Zend_Config_Ini('../application/configs/application.ini', 'production');
        $host = $config->resources->db->params->host;
        $port = $config->resources->db->params->port;
        $dbname = $config->resources->db->params->dbname;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;

        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => $host,  
            'username' => $user,  
            'password' => $pass,  
            'dbname' => $dbname        
        ));
        
        
       $select = "select A.nombre, A.apellido, C.entrada, C.salida ".
	"from personas as A ".
	"inner join rol_x_persona as B ". 
	"on B.id_persona = A.id_persona ".
	"inner join marcaciones_funcionarios as C ".
	"on C.id_rol_x_persona = B.id_rol_x_persona";
       
       try {
            $result = $db->fetchAll($select);
            $this->_debugLogger->debug(print_r($result, true));  
            $json = array(
                "people" => $result
            );
            
            $this->_debugLogger->debug(print_r(Zend_Json_Encoder::encode($json), true));
            
       } catch (Exception $exc) {
           $this->_debugLogger->debug($exc->getMessage());
       }
       
    }
    
    public function getlistcheckinoutteachersAction(){
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $config = new Zend_Config_Ini('../application/configs/application.ini', 'production');
        $host = $config->resources->db->params->host;
        $port = $config->resources->db->params->port;
        $dbname = $config->resources->db->params->dbname;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;

        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => $host,  
            'username' => $user,  
            'password' => $pass,  
            'dbname' => $dbname        
        ));
        
        
       $select = "select A.nombre, A.apellido, C.nombre as nombre_materia, D.entrada, D.salida  ".
	"from personas as A ".
	"inner join rol_x_persona as B ".
	"on B.id_persona = A.id_persona ".
	"and B.id_rol = 2 ".
	"inner join materias as C ".
	"on C.id_rol_x_persona = B.id_rol_x_persona ".
	"inner join marcaciones_profesores as D ".
	"on D.id_materia = C.id_materia";
       
       try {
            $result = $db->fetchAll($select);
            $this->_debugLogger->debug(print_r($result, true));  
            $json = array(
                "people" => $result
            );
            
            $this->_debugLogger->debug(print_r(Zend_Json_Encoder::encode($json), true));
            
       } catch (Exception $exc) {
           $this->_debugLogger->debug($exc->getMessage());
       }
       
    }
    
    public function listteachersAction(){
        
    }
    
    public function listworkersAction(){
        
    }
    
    private function checkUser($email, $password){
       /*Verifica que el usuario que va a ser login sea administrador 
        * para poder hacer el registro de usuario nuevo
        */
       $config = new Zend_Config_Ini('../application/configs/application.ini', 'production');

        $host = $config->resources->db->params->host;
        $port = $config->resources->db->params->port;
        $dbname = $config->resources->db->params->dbname;
        $user = $config->resources->db->params->username;
        $pass = $config->resources->db->params->password;

//        $this->_debugLogger->debug("Host " . $host);
//        $this->_debugLogger->debug("Port ". $port);
//        $this->_debugLogger->debug("Ddname ". $dbname);
//        $this->_debugLogger->debug("User ". $user);
//        $this->_debugLogger->debug("Pass ". $pass);
        
        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => $host,  
            'username' => $user,  
            'password' => $pass,  
            'dbname' => $dbname        
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

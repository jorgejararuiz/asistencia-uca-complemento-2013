<?php

class IndexController extends Zend_Controller_Action
{

    private $_debugLogger;
    private $_errorLogger;
    private $_infoLogger;
    public function init(){
        //$this->_helper->layout->disableLayout();
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG); 
    }

    public function indexAction(){
        
    }

    public function asistenciafuncionariosAction(){
        
    try {
        
        //Variable para establecer si se hizo o no el registro
        //en la DB de la marcación
        $errorRegistering = false;
        
        if($this->getRequest()->isPost()){
            $this->_debugLogger->debug("isPost");
            $username = $this->getRequest()->getParam('username');
            $this->_debugLogger->debug("Username: ".$username);
            if (trim($username) != "") {
                if($this->checkUnique($username) == true){
                    $userId = $this->obtainUserId($username);
                    if($userId['id_rol_x_persona'] != -1){
                        //isCheckOut se le pasa 1 para decir que es Funcionario
                        //como segundo parametro
                        if($this->isCheckOut($userId['id_rol_x_persona'], 1)){
                            $errorRegistering = $this->insertCheckOutWorkers($userId['id_rol_x_persona']);
                        }else{
                            $errorRegistering = $this->insertRegisterWorkers($userId['id_rol_x_persona']);
                        }
                    }else{
                    $errorRegistering = true;
                    }    
                }else{
                    $errorRegistering = true;
                }
            }else{
                $errorRegistering = true;
            }               
        }
        //En caso de que haya habido un error en el registro
        //Se despliega un error y se redirecciona al Index
        if ($errorRegistering == true){
            $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Authentication error!</div>";
            $this->_debugLogger->debug("Error de Autenticacion");
            //$this->_redirect("index");
        }
        
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
        $this->_debugLogger->debug($exc->getTraceAsString());
    }

        
        
    }
    public function asistenciaprofesoresAction(){
    
        //Variable para establecer si se hizo o no el registro
        //en la DB de la marcación
        $errorRegistering = false;
        
        if($this->getRequest()->isPost()){
            $this->_debugLogger->debug("isPost");
            $username = $this->getRequest()->getParam('username');
            $materia = $this->getRequest()->getParam('materia');
            $this->_debugLogger->debug("Username: ".$username);
            $this->_debugLogger->debug("Materia: ". $materia);
            if (trim($username) != "" && !is_null($materia)) {
                if($this->checkUnique($username) == true){
                    //Se le pasa 0 como segundo parametro para indicar que 
                    //es profesor
                    if($this->isCheckOut($materia, 0)){
                        $horario = 1;
                        $errorRegistering = $this->insertCheckOutTeacher($horario, $materia);
                    }else{
                        $horario = 1;
                        $errorRegistering = $this->insertRegisterTeacher($horario, $materia);
                    
                    }
                }else{
                    $errorRegistering = true;
                }
            }else{
                $errorRegistering = true;
            }               
        }
        //En caso de que haya habido un error en el registro
        //Se despliega un error y se redirecciona al Index
        if ($errorRegistering == true){
            $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Authentication error!</div>";
            $this->_debugLogger->debug("Error de Autenticacion");
            //$this->_redirect("index");
        }
        
    }
    
    private function checkUnique($email){

        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $email)){
            $select = $db->select()
                ->from(array('public.personas'), array('email'))
                ->where("email = ?", $email);
            
            $this->_debugLogger->debug($select);
            $result = $db->fetchRow($select);

            if($result){
                return true;
            }
                return false;
            
        }else{
            $select = $db->select()
                ->from(array('public.personas'), array('email'))
                ->where("ci = ?", intval($email));
            $this->_debugLogger->debug($select);
            $result = $db->fetchRow($select);
            
            if($result){
                return true;
            }
                return false;
        }
        
    }
    
    /*
     * select ROL.id_rol_x_persona from rol_x_persona as ROL 
	inner join personas as PERSONAS
	on PERSONAS.email = 'sergio@uca.edu.py'
	and PERSONAS.id_persona = ROL.id_persona;
     */
    private function obtainUserId($username){
        
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));

        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $username)){
            $select = "select ROL.id_rol_x_persona from rol_x_persona as ROL "
                        ."inner join personas as PERSONAS "
                        ."on PERSONAS.email = '". $username 
                        ."' and PERSONAS.id_persona = ROL.id_persona;";

            $this->_debugLogger->debug($select);
            try {
            //Intenta meter en la DB
                $result = $db->fetchRow($select);
            } catch (Exception $exc) {
                $this->_debugLogger->debug($exc->getTraceAsString());
            }

            if($result){
                return $result;
            }
                return -1;
        }else{
            $select = "select ROL.id_rol_x_persona from rol_x_persona as ROL "
                        ."inner join personas as PERSONAS "
                        ."on PERSONAS.ci = ". intval($username)
                        ." and PERSONAS.id_persona = ROL.id_persona;";

            $this->_debugLogger->debug($select);
            try {
            //Intenta meter en la DB
                $result = $db->fetchRow($select);
            } catch (Exception $exc) {
                $this->_debugLogger->debug($exc->getTraceAsString());
            }

            if($result){
                return $result;
            }
                return -1;
        }
        
    }
    private function insertRegisterTeacher($horario, $materia){
        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        
        $insert = "insert into marcaciones_profesores "
                ."(entrada, salida, id_horario, id_materia) values "
                ."(now(), now(), ". $horario .", ". $materia.")";
   
        $this->_debugLogger->debug($insert);
        try {
        //Intenta meter en la DB
            $result = $db->fetchRow($insert);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if($result){
            return true;
        }
            return false;

    }
    
    private function isCheckOut($userId, $isTeacher){
       //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        $select = "";
        
        if($isTeacher == 0){
            $select = "select * from marcaciones_profesores where "
                      . " entrada > (now() - interval '24 hours') "
                      . " and entrada = salida "
                      . " and id_materia = ".$userId;
        }else{
            $select = "select * from marcaciones_funcionarios where "
                      . " entrada > (now() - interval '24 hours') "
                      . " and entrada = salida "
                      . " and id_rol_x_persona = ".$userId;
        }        
        
        $this->_debugLogger->debug($select);
        try {
        //Intenta meter en la DB
            $result = $db->fetchRow($select);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if($result){
            return true;
        }
            return false;
    }
     
    
    
    private function insertRegisterWorkers($userId){
        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        
        $insert = "insert into marcaciones_funcionarios "
                ."(entrada, salida, id_rol_x_persona) values "
                ."(now(), now(),". $userId.")";
        
        
        $this->_debugLogger->debug($insert);
        try {
        //Intenta meter en la DB
            $result = $db->fetchRow($insert);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if($result){
            return true;
        }
            return false;
    }
    
    private function insertCheckOutWorkers($userId){
        
        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        
        $insert = "update marcaciones_funcionarios set salida = now() "
                  . "  where entrada = salida"
                  . "  and id_rol_x_persona = " . $userId;
        
        
        $this->_debugLogger->debug($insert);
        try {
        //Intenta meter en la DB
            $result = $db->fetchRow($insert);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if($result){
            return true;
        }
            return false;
        
    }
    private function insertCheckOutTeacher($horario, $materia){
        
        //Obtiene la Configuracion de la DB
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
             'host' => 'localhost',  
            'username' => 'admin',  
            'password' => 'admin',  
            'dbname' => 'asistenciaUCA'        
        ));
        
        $insert = "update marcaciones_profesores set salida = now() "
                  . "  where entrada = salida"
                  . "  and id_horario = " . $horario
                  . "  and id_materia = " . $materia;
        
        
        $this->_debugLogger->debug($insert);
        try {
        //Intenta meter en la DB
            $result = $db->fetchRow($insert);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if($result){
            return true;
        }
            return false;
        
    }
    
}

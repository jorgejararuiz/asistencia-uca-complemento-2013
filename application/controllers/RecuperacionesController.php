<?php

class RecuperacionesController extends Zend_Controller_Action
{
    private $_debugLogger;
    
    public function init()
    {
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG); 
    }

    public function indexAction(){
        
         //Variable para establecer si se hizo o no el registro
        //en la DB de la marcación
        $errorRegistering = false;

        if ($this->getRequest()->isPost())
        {
            $this->_debugLogger->debug("isPost");
            $username = $this->getRequest()->getParam('username');
            $materia = $this->getRequest()->getParam('materia');
            $this->_debugLogger->debug("Username: " . $username);
            $this->_debugLogger->debug("Materia: " . $materia);
            if (trim($username) != "" && !is_null($materia))
            {
                if ($this->checkUnique($username) == true)
                {
                    //Se le pasa 0 como segundo parametro para indicar que 
                    //es profesor
                    if ($this->isCheckOut($materia, 0))
                    {
                        $horario = 1;
                        $errorRegistering = $this->insertCheckOutTeacher($horario, $materia);
                    }
                    else
                    {
                        $horario = 1;
                        $errorRegistering = $this->insertRegisterTeacher($horario, $materia);
                    }
                }
                else
                {
                    $errorRegistering = true;
                }
            }
            else
            {
                $errorRegistering = true;
            }
        }
        //En caso de que haya habido un error en el registro
        //Se despliega un error y se redirecciona al Index
        if ($errorRegistering == true)
        {
            $this->view->loginError = "<div class='alert alert-block alert-error fade in' id='value-alert'>Authentication error!</div>";
            $this->_debugLogger->debug("Error de Autenticacion");
            //$this->_redirect("index");
        }
        
        
    }

    public function registrarrecuperacionAction(){
        
    }
    
    
    private function checkUnique($email)
    {


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

        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($regex, $email))
        {
            $select = $db->select()
                    ->from(array('public.personas'), array('email'))
                    ->where("email = ?", $email);

            $this->_debugLogger->debug(print_r($select, true));
            $result = $db->fetchRow($select);

            if ($result)
            {
                return true;
            }
            return false;
        }
        else
        {
            $select = $db->select()
                    ->from(array('public.personas'), array('email'))
                    ->where("ci = ?", intval($email));
            $this->_debugLogger->debug($select);
            $result = $db->fetchRow($select);

            if ($result)
            {
                return true;
            }
            return false;
        }
    }
    
    private function isCheckOut($userId, $isTeacher)
    {

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

        $select = "";

        if ($isTeacher == 0)
        {
            $select = "select * from marcaciones_profesores where "
                    . " entrada > (now() - interval '24 hours') "
                    . " and entrada = salida "
                    . " and id_materia = " . $userId;
        }
        else
        {
            $select = "select * from marcaciones_funcionarios where "
                    . " entrada > (now() - interval '24 hours') "
                    . " and entrada = salida "
                    . " and id_rol_x_persona = " . $userId;
        }

        $this->_debugLogger->debug($select);
        try {
            //Intenta meter en la DB
            $result = $db->fetchRow($select);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if ($result)
        {
            return true;
        }
        return false;
    }
    
    private function insertCheckOutTeacher($horario, $materia)
    {

        //Obtiene la Configuracion de la DB
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

        if ($result)
        {
            return true;
        }
        return false;
    }
    
    private function insertRegisterTeacher($horario, $materia)
    {

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


        $insert = "insert into marcaciones_profesores "
                . "(entrada, salida, id_horario, id_materia) values "
                . "(now(), now(), " . $horario . ", " . $materia . ")";

        $this->_debugLogger->debug($insert);
        try {
            //Intenta meter en la DB
            $result = $db->fetchRow($insert);
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }

        if ($result)
        {
            return true;
        }
        return false;
    }
}





?>

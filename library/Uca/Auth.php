<?php

/**
 * Description of Auth
 *
 * @author jorgejararuiz
 */
class Uca_Auth {

    private $username;
    private $password;
    private $_debugLogger;

    function __construct($user, $pass) {
        $this->username = $user;
        $this->password = md5($pass);
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
    }

    private function getUserInfo() {
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
                     'host' => 'localhost',  
                    'username' => 'admin',  
                    'password' => 'admin',  
                    'dbname' => 'asistenciaUCA'        
        ));
        $userInfo = array();
        // client data
//        $select = $db->select()
//                ->from(array('public.personas'), array('nombre', 'apellido', 'ci', 'telefono', 'fecha_nacimiento' ,'sexo', 'email'))
//                ->where("email = ?", $this->username);
        $select = "SELECT * FROM personas AS PERSONAS where PERSONAS.email = '". $this->username ."'";
       

        $result = $db->fetchRow($select);
        $userInfo["nombre"] = $result["nombre"];
        $userInfo["apellido"] = $result["apellido"];
        $userInfo["username"] = $this->username;
        $userInfo["email"] = $result["email"];
        $userInfo["password"] = $result["password"];
        $userInfo["ci"] = $result["ci"];
        $userInfo["telefono"] = $result["telefono"];
        
        return $userInfo;
    }

    public function authenticate() {
        
        $this->_debugLogger->debug("authenticate user");
        $this->_debugLogger->debug("username: ". $this->username);
        $this->_debugLogger->debug("password: ". $this->password);
        
        $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
                     'host' => 'localhost',  
                    'username' => 'admin',  
                    'password' => 'admin',  
                    'dbname' => 'asistenciaUCA'        
        ));
        
        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $authAdapter->setTableName(array('personas'))
                ->setIdentityColumn('email')
                ->setCredentialColumn('password')
                ->setIdentity($this->username)
                ->setCredential($this->password);
       
        $auth = Zend_Auth::getInstance();

        
        try {
            $result = $auth->authenticate($authAdapter);
            if ($result->isValid()) {
                // the default storage is a session with namespace Zend_Auth  
                $authStorage = $auth->getStorage();
                $authStorage->write($this->getUserInfo());
                return true;
            }
            return false;
        } catch (Exception $exc) {
            $this->_debugLogger->debug($exc->getTraceAsString());
        }
        
        return false;
    }

}

?>

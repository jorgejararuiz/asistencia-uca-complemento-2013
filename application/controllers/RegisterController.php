<?php

class RegisterController extends Zend_Controller_Action {

    private $_debugLogger;
    private $_errorLogger;
    private $_infoLogger;

    public function init()
    {
        $messages = $this->_helper->flashMessenger->getMessages();
        if (!empty($messages))
            $this->_helper->layout->getView()->message = $messages[0];
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG);
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity())
        {
            $this->_redirect("index");
        }
        $this->userInfo = $auth->getIdentity();
    }

    public function homeAction()
    {
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if (!$data)
        {
            $this->_redirect('index');
        }
        $this->view->username = $data->username;
    }

    public function signupAction()
    {
        /* TODO
         * Implementar el guardado o registro de usuarios nuevos
         */
        $this->_helper->layout->disableLayout();
        $users = new Users();
        $form = new Application_Form_RegistrationForm();
        $form->setDecorators(array(
            'FormElements',
            array(array('data' => 'HtmlTag'), array('tag' => 'table')),
            'Form'));
        header("Content-Type: text/html;charset=utf-8");
        $this->view->form = $form;

        if ($this->getRequest()->isPost())
        {
            if ($form->isValid($_POST))
            {
                $this->_debugLogger->debug("Form is Valid");
                //$form->removeElement('contrasenha2');
                $data = $form->getValues();
                if ($data['password'] != $data['contrasenha2'])
                {
                    $this->view->errorMessage = "Las contraseñas deben coincidir";
                    return;
                }
                if ($users->checkUnique($data['email']))
                {
                    $this->view->errorMessage = "El email ya esta registrado";
                    return;
                }
                $data['password'] = md5($data['password']);
                unset($data['contrasenha2']);
                $idRol = $data['id_rol'];
                unset($data['id_rol']);
//                   try {
                $this->_debugLogger->debug("About to insert");
                $users->insertUser($data, $idRol);
                $this->_debugLogger->debug("Inserted");
                $this->addRol($idRol, $data['email']);
                 
                //Manda Mail de confirmación
//                   $config = array('auth' => 'login',
//                        'port' => '26',
//                        'username' => 'support@pearljamparaguay.com',
//                        'password' => 'AwWmJc]U))UF');
//                    $tr = new Zend_Mail_Transport_Smtp('mail.pearljamparaguay.com', $config);
//                    Zend_Mail::setDefaultTransport($tr);
//                    Zend_Mail::setDefaultFrom('support@pearljamparaguay.com', 'Soporte Virtual Labs Manager');
//                    Zend_Mail::setDefaultReplyTo('support@pearljamparaguay.com', 'Soporte Virtual Labs Manager');
//                    $mail = new Zend_Mail();
//                    $mail->addTo($data['email']);
//                    $mail->setSubject('Confirmación de Registro');
//                    $msg = utf8_encode("Estimado ". $data['nombre'] .":\n Usted se ha registrado satisfactoriamente a Asistencia UCA. \nGracias.");
//                    $mail->setBodyHtml($msg);
//                    $mail->send($tr);
//                    $this->_infoLogger->info("[Usuario Registrado] Mail:".$data['email']);
//                    $this->_redirect('login');
//                    } catch (Exception $exc) {
//                       $this->_errorLogger->error($exc->getTraceAsString());
//                       echo $exc->getTraceAsString();
                $this->_redirect('index');
//                   }
            }
        }
    }

    private function addRol($idRol, $email)
    {

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

        $select = $db->select()
                ->from(array('personas'), array('id_persona'))
                ->where("email = ?", $email);
        $result = $db->fetchRow($select);
        
        $array = array(
            'id_persona' => $result['id_persona'],
            'id_rol' => $idRol
        );
        $insert = $db->insert('rol_x_persona', $array);

        return true;
    }

}

class Users extends Zend_Db_Table_Abstract {

    protected $_name = "personas";

    function checkUnique($email)
    {

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

        $authAdapter = new Zend_Auth_Adapter_DbTable($db);
        $select = $db->select()
                ->from(array('personas'), array('email'))
                ->where("email = ?", $email);
        $result = $db->fetchRow($select);
        if ($result)
        {
            return true;
        }
        return false;
    }

    function insertUser($data, $idRol)
    {

        //inserta a la persona
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

        $insert = $db->insert('personas', $data);
        
    }

//   function insertRol($idPersona, $idRol){
//    $db = new Zend_Db_Adapter_Pdo_Pgsql(array(
//             'host' => 'localhost',  
//            'username' => 'admin',  
//            'password' => 'admin',  
//            'dbname' => 'asistenciaUCA'        
//    ));
//       
//       //Inserta el rol de la persona
//    $select = $db->select()
//        ->from(array('personas'), array('id_persona'))
//        ->where("email = ?", $data['email']);
//    $result = $db->fetchRow($select);
//    
//    $db->insert('rol_x_persona', array('id_rol'=>$idRol, 'id_persona'=>$result['id_persona']));
//    
//   }
}

class Application_Form_RegistrationForm extends Zend_Form {

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        $horizontalDecorators = array(
            'ViewHelper',
            'Description',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        );

        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('Este campo no puede ser nulo!');

        $firstname = $this->createElement('text', 'nombre');
        $firstname->setLabel('Nombre: ')
                ->setDecorators($horizontalDecorators)
                ->setRequired(true)
                ->addValidator($notEmpty, true);

        $lastname = $this->createElement('text', 'apellido');
        $lastname->setLabel('Apellido: ')
                ->setDecorators($horizontalDecorators)
                ->addValidator($notEmpty, true)
                ->setRequired(true);

        $email = $this->createElement('text', 'email');
        $email->setLabel('Email: ')
                ->setRequired(true)
                ->setDecorators($horizontalDecorators)
                ->addValidator($notEmpty, true)
                ->addValidator('EmailAddress');

        $passwordValidator = new MyValid_PasswordStrength();

        $password = $this->createElement('password', 'password');
        $password->setLabel('Contraseña: ')
                ->setDecorators($horizontalDecorators)
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->addValidator($passwordValidator);


        $confirmPassword = $this->createElement('password', 'contrasenha2');
        $confirmPassword->setLabel('Confirmar Contraseña: ')
                ->setDecorators($horizontalDecorators)
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->addValidator($notEmpty, true)
                ->addValidator($passwordValidator);

        $numeroDeDocumento = $this->createElement('text', 'ci');
        $numeroDeDocumento->setLabel("Número de Documento:")
                ->setDecorators($horizontalDecorators)
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->addValidator('Digits');

        $telefono = $this->createElement('text', 'telefono');
        $telefono->setLabel('Teléfono: ')
                ->setDecorators($horizontalDecorators)
                ->addValidator($notEmpty, true)
                ->setRequired(true);

        $decorator = array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        );
        $register = $this->createElement('submit', 'register');
        $register->setLabel('Registrar')
                ->setDecorators($decorator)
                ->addValidator($notEmpty, true)
                ->setIgnore(true);

        $rol = $this->createElement('select', 'id_rol');
        $rol->setLabel('Rol: ')
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->setDecorators($horizontalDecorators)
                ->addMultiOptions(array(
                    '2' => 'Profesor',
                    '3' => 'Funcionario'
        ));

        $sexo = $this->createElement('select', 'sexo');
        $sexo->setLabel('Sexo: ')
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->setDecorators($horizontalDecorators)
                ->addMultiOptions(array(
                    'M' => 'Masculino',
                    'F' => 'Femenino'
        ));

        $this->addElements(array(
            $firstname,
            $lastname,
            $email,
            $rol,
            $sexo,
            $password,
            $confirmPassword,
            $numeroDeDocumento,
            $telefono,
            $register
        ));
    }

}

class My_Decorator_SimpleInput extends Zend_Form_Decorator_Abstract {

    protected $_format = '<button  class="btn btn-primary" ><i class="icon-hand-right icon-white"></i>Registrarse</button>';

    public function render($content)
    {
        $element = $this->getElement();
        $name = htmlentities($element->getFullyQualifiedName());
        $label = htmlentities($element->getLabel());
        $id = htmlentities($element->getId());
        $value = htmlentities($element->getValue());

        $markup = sprintf($this->_format, $name, $label, $id, $name, $value);
        return $markup;
    }

}

class My_Decorator_InputFormText extends Zend_Form_Decorator_Abstract {

    protected $_format = '<button class="btn dropdown-toggle" data-toggle="dropdown">';

    public function render($content)
    {
        $element = $this->getElement();
        $name = htmlentities($element->getFullyQualifiedName());
        $label = htmlentities($element->getLabel());
        $id = htmlentities($element->getId());
        $value = htmlentities($element->getValue());

        $markup = sprintf($this->_format, $name, $label, $id, $name, $value);
        return $markup;
    }

}

require_once 'Zend/Validate/Abstract.php';

class MyValid_PasswordStrength extends Zend_Validate_Abstract {

    const LENGTH = 'length';
    const UPPER = 'upper';
    const LOWER = 'lower';
    const DIGIT = 'digit';

    protected $_messageTemplates = array(
        self::LENGTH => "'%value%' debe tener al menos 8 caracteres",
        self::UPPER => "'%value%' debe contener un caracter en mayúscula",
        self::LOWER => "'%value%' debe contener al menos un caracter en minúscula",
        self::DIGIT => "'%value%' debe contener al menos un digito"
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        $isValid = true;

        if (strlen($value) < 8)
        {
            $this->_error(self::LENGTH);
            $isValid = false;
        }

        if (!preg_match('/[A-Z]/', $value))
        {
            $this->_error(self::UPPER);
            $isValid = false;
        }

        if (!preg_match('/[a-z]/', $value))
        {
            $this->_error(self::LOWER);
            $isValid = false;
        }

        if (!preg_match('/\d/', $value))
        {
            $this->_error(self::DIGIT);
            $isValid = false;
        }

        return $isValid;
    }

}
?>


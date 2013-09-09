<?php
class RegisterController extends Zend_Controller_Action
{
    private $_debugLogger;
    private $_errorLogger;
    private $_infoLogger;
    
    public function init()
    {
        $messages = $this->_helper->flashMessenger->getMessages();
        if(!empty($messages))
            $this->_helper->layout->getView()->message = $messages[0];
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG);
    
    }
    public function preDispatch() {
        parent::preDispatch();
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect("index");
        }
        $this->userInfo = $auth->getIdentity();
    }
    
    public function homeAction()
    {
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if(!$data){
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
        //$users = new Users();
        $form = new Application_Form_RegistrationForm();
        $form->setDecorators(array(
               'FormElements',
               array(array('data'=>'HtmlTag'),array('tag'=>'table')),
               'Form'));
        header("Content-Type: text/html;charset=utf-8");
        $this->view->form=$form;

//           if($this->getRequest()->isPost()){
//               if($form->isValid($_POST)){
//                   //$form->removeElement('contrasenha2');
//                   $data = $form->getValues();
//                   if($data['contrasenha'] != $data['contrasenha2']){
//                       $this->view->errorMessage = "Las contraseñas deben coincidir";
//                       return;
//                   }
//                   if($users->checkUnique($data['email'])){
//                       $this->view->errorMessage = "El email ya esta registrado";
//                       return;
//                   }
//                   $data['contrasenha'] = md5($data['contrasenha']);
//                   unset($data['contrasenha2']);
//                   
//                   try {
//                   $users->insert($data);
//                   //Manda Mail de confirmación
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
//                    $msg = utf8_encode("Estimado ". $data['nombre'] .":\n Usted se ha registrado satisfactoriamente a Ticketshow. \nGracias.");
//                    $mail->setBodyHtml($msg);
//                    $mail->send($tr);
//                    $this->_infoLogger->info("[Usuario Registrado] Mail:".$data['email']);
//                    $this->_redirect('login');
//                    } catch (Exception $exc) {
//                       $this->_errorLogger->error($exc->getTraceAsString());
//                       echo $exc->getTraceAsString();
//                       $this->_redirect('login');
//                   }
//               }
//           } 
         
       }
}




class Users extends Zend_Db_Table_Abstract
{

    protected $_name="web.web_users";
    
    function checkUnique($email){
       
       $db = Zend_Db_Table_Abstract::getDefaultAdapter();
       $authAdapter = new Zend_Auth_Adapter_DbTable($db);
       $select = $db->select()
                ->from(array('web.web_users'), array('email'))
                ->where("email = ?", $email);
       $result = $db->fetchRow($select);
       
       if($result){
           return true;
       }
       return false;
   }

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
                   array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                   array('Label', array('tag' => 'td')),
                   array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
                    );
        
        $notEmpty = new Zend_Validate_NotEmpty();
        $notEmpty->setMessage('Este campo no puede ser nulo!');    
        
        $firstname = $this->createElement('text','nombre');
        $firstname->setLabel('Nombre: ')
                ->setDecorators($horizontalDecorators)  
                ->setRequired(true)
                ->addValidator($notEmpty, true);
                    
        $lastname = $this->createElement('text','apellido');
        $lastname->setLabel('Apellido: ')
                ->setDecorators($horizontalDecorators)
                ->addValidator($notEmpty, true)
                ->setRequired(true);
                    
        $email = $this->createElement('text','email');
        $email->setLabel('Email: ')
                ->setRequired(true)
                ->setDecorators($horizontalDecorators)
                ->addValidator($notEmpty, true)
                ->addValidator('EmailAddress');
        
        $passwordValidator = new MyValid_PasswordStrength();
        
        $password = $this->createElement('password','contrasenha');
        $password->setLabel('Contraseña: ')
                ->setDecorators($horizontalDecorators) 
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->addValidator($passwordValidator);
        
                
        $confirmPassword = $this->createElement('password','contrasenha2');
        $confirmPassword->setLabel('Confirmar Contraseña: ')
                ->setDecorators($horizontalDecorators) 
                ->setRequired(true)
                ->addValidator($notEmpty, true)
                ->addValidator($notEmpty, true)
                ->addValidator($passwordValidator);
                
        $numeroDeDocumento = $this->createElement('text', 'numero_documento');
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

        $celular = $this->createElement('text', 'celular');
        $celular->setLabel('Celular: ')
                ->setDecorators($horizontalDecorators) 
                ->setRequired(false);
        
        $decorator = array(
                   'ViewHelper',
                   'Errors',
                   array(array('data'=>'HtmlTag'), array('tag' => 'td')),
                   array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
                    );
        $register = $this->createElement('submit','register');
        $register->setLabel('Registrar')
                ->setDecorators($decorator)
                ->addValidator($notEmpty, true)
                ->setIgnore(true);
        
            
        $this->addElements(array(
                        $firstname,
                        $lastname,
                        $email,
                        $password,
                        $confirmPassword,
                        $numeroDeDocumento,
                        $telefono,
                        $celular,
                        $register
        ));
    }
}

class My_Decorator_SimpleInput extends Zend_Form_Decorator_Abstract
{
    protected $_format = '<button  class="btn btn-primary" ><i class="icon-hand-right icon-white"></i>Registrarse</button>';

    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $label   = htmlentities($element->getLabel());
        $id      = htmlentities($element->getId());
        $value   = htmlentities($element->getValue());
 
        $markup  = sprintf($this->_format, $name, $label, $id, $name, $value);
        return $markup;
    }
}

class My_Decorator_InputFormText extends Zend_Form_Decorator_Abstract
{
    protected $_format = '<button class="btn dropdown-toggle" data-toggle="dropdown">';
    
    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $label   = htmlentities($element->getLabel());
        $id      = htmlentities($element->getId());
        $value   = htmlentities($element->getValue());
 
        $markup  = sprintf($this->_format, $name, $label, $id, $name, $value);
        return $markup;
    }
}

require_once 'Zend/Validate/Abstract.php';


class MyValid_PasswordStrength extends Zend_Validate_Abstract
{
    const LENGTH = 'length';
    const UPPER  = 'upper';
    const LOWER  = 'lower';
    const DIGIT  = 'digit';

    protected $_messageTemplates = array(
        self::LENGTH => "'%value%' debe tener al menos 8 caracteres",
        self::UPPER  => "'%value%' debe contener un caracter en mayúscula",
        self::LOWER  => "'%value%' debe contener al menos un caracter en minúscula",
        self::DIGIT  => "'%value%' debe contener al menos un digito"
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        $isValid = true;

        if (strlen($value) < 8) {
            $this->_error(self::LENGTH);
            $isValid = false;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $this->_error(self::UPPER);
            $isValid = false;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $this->_error(self::LOWER);
            $isValid = false;
        }

        if (!preg_match('/\d/', $value)) {
            $this->_error(self::DIGIT);
            $isValid = false;
        }

        return $isValid;
    }
}

?>


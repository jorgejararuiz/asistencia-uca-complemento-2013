<?php

class RecuperacionesController extends Zend_Controller_Action
{
    private $_debugLogger;
    private $_errorLogger;
    private $_infoLogger;
    
    public function init()
    {
        $this->_debugLogger = Zend_Registry::get(Uca_Common::DEBUG_LOG);
        $this->_errorLogger = Zend_Registry::get(Uca_Common::ERROR_LOG);
        $this->_infoLogger = Zend_Registry::get(Uca_Common::INFO_LOG); 
    }

    public function indexAction(){
        
    }

    
    
    
    
}





?>

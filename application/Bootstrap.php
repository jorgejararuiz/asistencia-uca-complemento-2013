<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
//    protected function _initSession()
//    {
//        //$session = new Zend_Session_Namespace('auth');
//        $session = $_SESSION;
//    }
    protected function _initView()
    {
        // Initialize view
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->headTitle('My First Zend Framework Application');
 
        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);
 
        // Return it, so that it can be stored by the bootstrap
        return $view;
    }
    
    public function _initLog() {
        $writerError = new Zend_Log_Writer_Stream($this->getOption('error_log'));
        $writerDebug = new Zend_Log_Writer_Stream($this->getOption('debug_log'));
        $writerInfo = new Zend_Log_Writer_Stream($this->getOption('info_log'));
        $logError = new Zend_Log($writerError);
        $logError->log('Error log created', Zend_Log::INFO);
        Zend_Registry::set(Uca_Common::ERROR_LOG, $logError);
        $logDebug = new Zend_Log($writerDebug);
        $logDebug->log('Debug log created', Zend_Log::INFO);
        Zend_Registry::set(Uca_Common::DEBUG_LOG, $logDebug);
        $logInfo = new Zend_Log($writerInfo);
        $logInfo->log('Info log created', Zend_Log::INFO);
        Zend_Registry::set(Uca_Common::INFO_LOG, $logInfo);
        
        // $infoLog = Zend_Registry::get(Ticketshow_Common::INFO_LOG);
    }
    
}
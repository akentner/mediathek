<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initRouter() {

        $this->bootstrap('frontcontroller');
        $front = $this->getResource('frontcontroller');

        if (PHP_SAPI == 'cli') {
            $front->setRouter(new Lexsign_Router_Cli());
            $front->setRequest(new Zend_Controller_Request_Simple());
        }
    }

    protected function _initRoutes() {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        if ($router instanceof Zend_Controller_Router_Rewrite) {
            // Specifying the "api" module only as RESTful:
            $restRoute = new Zend_Rest_Route($front, array(), array('api'));
            $router->addRoute('rest', $restRoute);
        }
    }

    protected function _initActionHelpers() {
        Zend_Controller_Action_HelperBroker::addHelper(
                new Lexsign_Controller_Helper_Params()
        );
        Zend_Controller_Action_HelperBroker::addHelper(
                new Lexsign_Controller_Helper_RestContexts()
        );
    }

    protected function _initError() {
        if (PHP_SAPI == 'cli') {
            $front = Zend_Controller_Front::getInstance();
            if (!$error = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler')) {
                $front->registerPlugin(
                    new Zend_Controller_Plugin_ErrorHandler(
                        array('controller' => 'error', 'action' => 'cli')
                    ), 
                    100
                );
            }
        }
    }

}


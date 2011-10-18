<?php

require_once(APPLICATION_PATH . '/modules/mediathek/models/RestDownload.php');
require_once(APPLICATION_PATH . '/modules/api/models/Download.php');

class Mediathek_DebugController extends Zend_Controller_Action {

    public function init() {
//        $this->_helper->viewRenderer->setNoRender();
//        $this->_helper->getHelper('layout')->disableLayout();
    }

    public function indexAction() {
        
        $wget = new Lexsign_Executable_Wget();
        $wget->setUrl('http://dev.mediathek.local/test.zip');
        $wget->setId('0815');
        $wget->setVerbose();
        $wget->setBackground();
//        $wget->execute();
        $info = $wget->getInfo();
        $this->view->wget = $info;
        
    }

    public function testFunc($name1, $name2) {
        $this->view->testFunc = 'meee' . $name1 . $name2;
    }
    
    public function testPostAction() {

        $client = new Zend_Http_Client();
        $client->setMethod(Zend_Http_Client::POST);
        $client->setUri('http://dev.mediathek.local/api/download');
        $client->setHeaders('Content-Type', 'application/json');
        $client->setHeaders('Accept', 'application/json');

        $data = array(
            'auth' => 'value',
            'url' => 'http://www.google.de',
            'auth' => 'value',
        );
        $client->setRawData(\Zend_Json::encode($data));
        $response = $client->request();


        //Dump headers
        $this->view->response = $response->getHeaders();
        $this->view->body = $response->getBody();
        $this->render('index');
    }

}


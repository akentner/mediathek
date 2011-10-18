<?php

class Api_QueueController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $file = APPLICATION_PATH . '/configs/queue.xml';
        $config = new Zend_Config(array(), true);
        $config->adapter = 'db';
        $config->name = 'test2';
//        $config->adapterNamespace = 'Lexsign_Queue_Adapter';
        
        $driverOptions = new Zend_Config(array(), true);
        $driverOptions->type = 'pdo_mysql';
        $driverOptions->host = 'localhost';
        $driverOptions->username = 'root';
        $driverOptions->password = 'ak77MTLakv';
        $driverOptions->dbname = 'mediathek';

        $config->driverOptions = $driverOptions;
        
        $writer = new Zend_Config_Writer_Xml();
        $writer->setConfig($config);
        $writer->setFilename($file);
        $writer->write();
        
        $config = new Zend_Config_Xml($file);
        $queue = new \Zend_Queue($config);
        $queue->setAdapter($config->adapter);
        $test = $queue->createQueue('test');
        
        
        $info = $queue->debugInfo();
        
        $queue->send('test');
        
       
        $this->view->info = $info;
//        $this->view->queue = $queue;
    }


}


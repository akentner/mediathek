<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RestDownload
 *
 * @author akentner
 */
class Mediathek_Model_RestDownload {

    /**
     *
     * @var Zend_Http_Client
     */
    protected $client;
    protected $initData = array();
    protected $uri = 'http://dev.mediathek.local/api/download/';
    protected $type = 'application/json';

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_FINISHED = 3;
    const STATUS_ARCHIVED = 4;
    const STATUS_ERROR = 5;

    public function __construct() {
        $this->init();
    }

    public function init() {
        $this->client = new Zend_Http_Client();
        $this->client->setUri($this->uri);
        $this->client->setHeaders('Content-Type', $this->type);
        $this->client->setHeaders('Accept', $this->type);
    }

    
    public function getNextDownloadForProcessing($status) {
        $this->client->setUri($this->uri);
        $response = $this->doRequest(
            Zend_Http_Client::GET, 
            array(
                'do' => 'getDownloadsByStatus',
                'status' => $status,
                'limit' => 1,
            )
        );
        return $this->getBody($response);
    }
    
    public function countDownloadsByStatus($status) {
        $this->client->setUri($this->uri);
        $response = $this->doRequest(
            Zend_Http_Client::GET, 
            array(
                'do' => 'countDownloadsByStatus',
                'status' => $status,
            )
        );
        return $this->getBody($response);
    }
    
    public function addDownload($url) {
        $response = $this->doRequest(
                Zend_Http_Client::POST, array('url' => $url)
        );
        return $this->getBody($response);
    }

    public function getDownloads(int $status = null) {
        $response = $this->doRequest(
                Zend_Http_Client::GET, array()
        );
        return $this->getBody($response);
    }

    public function getDownload($id) {
        $this->client->setUri($this->uri . 'id/' . $id);
        $response = $this->doRequest(
                Zend_Http_Client::GET, array()
        );
        return $this->getBody($response);
    }

    public function delDownload($id) {
        $response = $this->doRequest(
                Zend_Http_Client::DELETE, array('id' => $id)
        );
        return $this->getBody($response);
    }

    public function setDownloadStatus($id, $status) {
        $response = $this->doRequest(
            Zend_Http_Client::PUT, 
            array(
                'do' => 'setStatus',
                'id' => $id,
                'status' => $status,
            )
        );
        return $this->getBody($response);
    }

    protected function doRequest($method, array $data) {
        $this->client->setMethod($method);
        $this->client->setRawData(
                \Zend_Json::encode(array_merge($this->initData, $data))
        );
        return $this->client->request();
    }

    protected function getBody($response) {
        try {
            return Zend_Json::decode($response->getBody());
        } catch (\Exception $e) {
            return $response->getBody();
        }
    }

}

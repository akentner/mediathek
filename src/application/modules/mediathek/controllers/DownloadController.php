<?php

require_once(APPLICATION_PATH . '/modules/mediathek/models/RestDownload.php');

class Mediathek_DownloadController extends Zend_Controller_Action {

    public function init() {
//        $this->_helper->viewRenderer->setNoRender();
//        $this->_helper->getHelper('layout')->disableLayout();
    }

    public function indexAction() {
        $model = new Mediathek_Model_RestDownload();
        $this->view->data = $model->getDownloads();
    }

    public function getAction() {
        $model = new Mediathek_Model_RestDownload();
        $this->view->data = $model->getDownload($this->_getParam('id'));
        $this->render('debug');
    }

    public function addAction() {
        $model = new Mediathek_Model_RestDownload();
        $url = 'http://www.lexsign.de';
        $this->view->data = $model->addDownload($url);
        $this->render('debug');
    }

    public function workflowAction() {
        $model = new Mediathek_Model_RestDownload();
        $id = $this->_getParam('id');
        $status = $this->_getParam('status');
        if (is_null($id) || is_null($status)) {
            throw new \Exception('you have to provide ID and new status');
        }
        $this->view->data = $model->setDownloadStatus($id, $status);
        $this->render('debug');
    }

    public function processQueueAction() {
        $model = new Mediathek_Model_RestDownload();
        $this->view->data = $model->processQueue();
    }

    public function cliAction() {
        /* @var $viewRenderer Zend_Controller_Action_Helper_ViewRenderer */
        $viewRenderer = $this->_helper->viewRenderer;
        $viewRenderer->setViewSuffix('cli.' . $viewRenderer->getViewSuffix());
        $this->_helper->getHelper('layout')->disableLayout();
        $params = $this->_getAllParams();

        switch (reset($params)) {
            case 'list':
                $this->indexAction();
                $this->render('index');
                break;
            case 'download':
                $this->doDownload();
                $this->render('debug');
                break;
            default:
                $this->error = '';
        }
    }

}


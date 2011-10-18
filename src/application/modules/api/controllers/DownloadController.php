<?php

require_once(APPLICATION_PATH . '/modules/api/models/Download.php');

class Api_DownloadController extends Zend_Rest_Controller {
    const QUEUE_NAME = 'download';

    protected $queue;

    public function init() {

        /* @var $cs Zend_Controller_Action_Helper_ContextSwitch */
        $cs = $this->_helper->getHelper('contextSwitch');

        $file = APPLICATION_PATH . '/configs/queue.xml';
        $options = array(
            'allowModifications' => true,
        );

        $config = new Zend_Config_Xml($file, APPLICATION_ENV, $options);
        $config->name = self::QUEUE_NAME;
        $this->queue = new \Zend_Queue($config);
        $this->queue->setAdapter($config->adapter);

        /* Initialize action controller here */
    }

    public function indexAction() {
        $this->handleSubActions('listQueue');
    }

    public function getAction() {
        if (!$id = $this->_getParam('id', false)) {
            // report error, redirect, etc.
        }
        try {
            $this->processQueueCheckJobs();
            $params = $this->_helper->params();
            $this->view->data = Api_Model_Download::find($id);
            $this->view->params = $params;
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    public function postAction() {
        $model = new Api_Model_Download();
        $params = $this->_getAllParams();
        $this->view->params = $params;

        try {
            $model->url = $params['url'];
            $save = $model->save();
            $id = (string) $save['upserted'];
            $this->view->data = Api_Model_Download::find($id);
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    public function putAction() {
        if (!$id = $this->_getParam('id', false)) {
            $this->view->success = false;
            $this->view->error = 'ID param not set';
        }
        try {
            $this->handleSubActions();
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    public function deleteAction() {
        if (!$id = $this->_getParam('id', false)) {
            // report error, redirect, etc.
        }
        ;
    }

    protected function handleSubActions($defaultMethod) {
        $this->view->params = $this->_getAllParams();
        $method = $defaultMethod;

        if ($this->_getParam('do')) {
            $method = $this->_getParam('do');
        }
        if (!method_exists($this, $method)) {
            throw new \Exception('subaction "' . $method . '" does not exists');
        }
        call_user_func(array($this, $method));
    }

    protected function setStatus($id, $status) {
        try {
            if (!$this->_getParam('id')) {
                throw new \Exception('id not set');
            }
            if (!$this->_getParam('status')) {
                throw new \Exception('status not set');
            }
            $model = Api_Model_Download::find($this->_getParam('id'));
            $model->setStatus($status);

            if (!$model->save()) {
                throw new \Exception('setStatus failed');
            }
            $this->view->data = $model;
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    protected function getDownloadsByStatus() {
        try {
            if (is_null($this->_getParam('status'))) {
                throw new \Exception('status not set');
            }
            $search = array('status' => $this->_getParam('status'));
            if ($this->_getParam('limit')) {
                $this->view->data = Api_Model_Download::all($search)
                        ->sort(array('created' => 1))
                        ->limit((int) $this->_getParam('limit'));
            } else {
                $this->view->data = Api_Model_Download::all($search)
                        ->sort(array('created' => 1));
            }
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    protected function listQueue() {
        try {
            $this->processQueueCheckJobs();
            $data = Api_Model_Download::all();
            $this->view->data = $data;
            $this->view->count = $data->count();
            $this->view->params = $this->_getAllParams();
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    protected function _getAllParams() {
        return array_merge($this->_helper->params(), parent::_getAllParams());
    }

    protected function _getParam($paramName, $default = null) {
        $helperParams = $this->_helper->params();
        return array_key_exists($paramName, $helperParams) ?
                $helperParams[$paramName] :
                parent::_getParam($paramName, $default);
    }

    protected function countDownloadsByStatus() {
        try {
            if (is_null($this->_getParam('status'))) {
                throw new \Exception('status not set');
            }
            $search = array('status' => $this->_getParam('status'));
            $data = Api_Model_Download::all($search)->count();
            $this->view->data = $data;
            $this->view->response = $this->_response;
            $this->view->success = true;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    protected function processQueue() {
        $this->processQueueCheckJobs();
//        $this->processQueueStartDownloads();
    }

    protected function processQueueStartDownloads() {
        try {
            $data = array();
            $search = array('status' => 'STATUS_PROCESSING');
            $countProcessing = Api_Model_Download::all($search)->count();

            $maxProcessing = 4;

            $this->view->params = $this->_getAllParams();
            $this->view->countProcessing = $countProcessing;
            $this->view->maxProcessing = $maxProcessing;
            $this->view->response = $this->_response;
            $this->view->success = true;
            if ($countProcessing < $maxProcessing) {
                $nextDownload = Api_Model_Download::one(array('status' => 'STATUS_NEW'));
                if (!$nextDownload instanceof Api_Model_Download) {
                    return;
                }
                $nextDownload->setStatus('STATUS_PROCESSING');
                $nextDownload->save();
                $this->view->nextDownload = $nextDownload;
                $wget = new Lexsign_Executable_Wget();
                $wget->setId($nextDownload->getId());
                $wget->setUrl($nextDownload->url);
                $wget->setVerbose();
                $wget->setBackground();
                $wget->execute();
            }
            $this->view->data = $data;
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    protected function processQueueCheckJobs() {
        try {
            $search = array('status' => 'STATUS_PROCESSING');
            $processing = Api_Model_Download::all($search);
            $this->view->params = $this->_getAllParams();
            $this->view->debug = array();
            foreach ($processing as $download) {
                $wget = new Lexsign_Executable_Wget();
                $info = $wget->getInfo($download->getId());
                $this->view->debug[] = $info;
                switch ($info['status']) {
                    case 'ERROR':
                        $download->setStatus('STATUS_ERROR');
                        break;
                    case 'DOWNLOADING':
                        $download->setStatus('STATUS_PROCESSING');
                        break;
                    case 'FINISHED':
                        $download->setStatus('STATUS_FINISHED');
                        break;
                }
                $download->save();
            }
            $this->view->response = $this->_response;
            $this->view->success = true;
            $this->view->data = array();
        } catch (\Exception $e) {
            $this->view->success = false;
            $this->view->error = $e;
        }
    }

    public function cliAction() {
        /* @var $viewRenderer Zend_Controller_Action_Helper_ViewRenderer */
        $viewRenderer = $this->_helper->viewRenderer;
        $viewRenderer->setViewSuffix('cli.' . $viewRenderer->getViewSuffix());
        $this->_helper->getHelper('layout')->disableLayout();
        $params = $this->_getAllParams();

        switch (reset($params)) {
            case 'processQueue':
                $this->processQueueCheckJobs();
//                $this->processQueueStartDownloads();
                $this->render('debug');
                break;
            default:
                $this->error = '';
        }
    }

}


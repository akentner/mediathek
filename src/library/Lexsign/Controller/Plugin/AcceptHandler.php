<?php
class Lexsign_Controller_Plugin_AcceptHandler
    extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {

        if (!$request instanceof Zend_Controller_Request_Http) {
            return;
        }

        $header = $request->getHeader('Accept');
        switch (true) {
            case (strstr($header, 'application/json')):
            case (strstr($header, 'text/json')):
                $request->setParam('format', 'json');
                break;
            case (strstr($header, 'application/xml')
                  && (!strstr($header, 'html'))):
                $request->setParam('format', 'xml');
                break;
            default:
                break;
        }
    }
}

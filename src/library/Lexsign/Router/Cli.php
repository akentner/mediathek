<?php

class Lexsign_Router_Cli extends Zend_Controller_Router_Abstract {

    public function route(Zend_Controller_Request_Abstract $dispatcher) {
        $getopt = new Zend_Console_Getopt(array());
        $arguments = $getopt->getRemainingArgs();

        if ($arguments) {
            $module = array_shift($arguments);
            $command = array_shift($arguments);
            if (!preg_match('~\W~', $command)) {
                $dispatcher->setModuleName($module);
                $dispatcher->setControllerName($command);
                $dispatcher->setActionName('cli');
                $dispatcher->setParams($arguments);
                unset($_SERVER ['argv'] [1]);
                return $dispatcher;
            }
            echo "Invalid command.\n", exit;
        }

        echo "No command given.\n", exit;
    }

    public function assemble($userParams, $name = null, $reset = false, $encode = true) {
        echo "Not implemented\n", exit;
    }

}

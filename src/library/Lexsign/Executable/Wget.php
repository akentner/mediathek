<?php

class Lexsign_Executable_Wget {

    protected $binary;
    protected $downloaDir;
    protected $logDir;
    protected $options = array();
    protected $url = '';
    protected $id = '';
    protected $dryRun = false;

    public function __construct() {
        $this->init();
    }

    public function init() {
        $filename = APPLICATION_PATH . '/configs/wget.ini';
        if (file_exists($filename)) {
            $config = new Zend_Config_Ini($filename, APPLICATION_ENV);
            foreach ($config->toArray() as $option => $value) {
                $this->$option = $value;
            }
        }
    }

    public function setDownloadDir($dir) {
        $this->downloadDir = $dir;
    }

    public function getDownloadDir() {
        if (is_null($this->downloadDir)) {
            throw new \Exception('downloadDir not set');
        }
        if (!is_writable($this->downloadDir)) {
            throw new \Exception('downloadDir "' . $this->downloadDir . '"is not writable');
        }
        return $this->downloadDir;
    }

    public function setLogDir($dir) {
        $this->logdir = $dir;
    }

    public function getLogDir() {
        if (is_null($this->logDir)) {
            throw new \Exception('logdir not set');
        }
        if (!is_writable($this->logDir)) {
            throw new \Exception('logdir "' . $this->logDir . '"is not writable');
        }
        return $this->logDir;
    }

    public function getCommand() {
        return $this->binary . ' ' . $this->getOptions(true) . ' ' . $this->url;
    }

    public function addOption($key, $value = null) {
        $this->options[$key] = $value;
    }

    public function addOptions($options) {
        $this->options = array_merge($this->options, $options);
    }

    public function removeOption($key) {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }
    }

    public function getOptions($asString = false) {
        if ($asString) {
            $str = '';
            foreach ($this->options as $key => $value) {
                switch ($key) {
                    case '-o':
                        $str .= $key . ' ' . $value . ' ';
                        break;
                    case '--limit-rate':
                        $str .= $key . '=' . $value . ' ';
                        break;
                    default:
                        $str .= $key . ' ';
                }
            }
            return $str;
        }
        return $this->options;
    }

    public function setId($id) {
        $this->id = $id;
        $this->setOutputFile($this->getLogFile($id));
    }

    public function getId() {
        return $this->id;
    }

    public function setBackground($bool = true) {
        if ($bool) {
            $this->addOption('-b');
        } else {
            $this->removeOption('-b');
        }
    }

    public function setVerbose($bool = true) {
        if ($bool) {
            $this->addOption('-v');
        } else {
            $this->removeOption('-v');
        }
    }

    public function setNonVerbose($bool = true) {
        if ($bool) {
            $this->addOption('-nv');
        } else {
            $this->removeOption('-nv');
        }
    }

    public function setOutputFile($file = null) {
        if ($file) {
            $this->addOption('-o', $file);
        } else {
            $this->removeOption('-o');
        }
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getUrl() {
        return $this->url;
    }

    public function execute($dryRun = false) {

        $dryRun = $dryRun || $this->dryRun;
        chdir($this->getDownloadDir());
        if ($dryRun) {
            echo 'PWD: ' . shell_exec('pwd') . "\n";
            echo 'COMMAND: ' . $this->getCommand() . "\n";
            return;
        }
        $debug = passthru($this->getCommand(), $return_var);
        echo $debug . "<br/>";
    }

    protected function parseLog($filename) {
        if (!file_exists($filename)) {
            throw new \Exception('file "' . $filename . '" does not exists');
        }
        $info = array();
        $handle = fopen($filename, 'r');
        $line = 0;
        while (!feof($handle)) {
            $line++;
            $buffer = fgets($handle);
            switch (true) {
                case ($line === 1);
                    // start time
                    preg_match('/(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $date = new Zend_Date($matches[1]);
                        $info['timestamp']['start'] = (int) $date->getTimestamp();
                        $info['timestamp']['end'] = (int) $date->getTimestamp();
                        unset($date);
                    }
                    unset($matches);
                    break;
                case ($line === 2);
                    // Resolving dev.mediathekw.local... failed: Name or service not known.
                    preg_match('/Resolving\s([\w\.]+)\.\.\.\s(\w+):\s(.+)/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $info['dns']['domain'] = $matches[1];
                    }
                    if (isset($matches[2])) {
                        $info['dns']['status'] = strtoupper($matches[2]);
                    }
                    if (isset($matches[3])) {
                        $info['dns']['message'] = $matches[3];
                    }
                    unset($matches);
                    preg_match('/Resolving\s([\w\.]+)\.\.\.\s(.+)/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $info['dns']['domain'] = $matches[1];
                    }
                    if (isset($matches[2])) {
                        $info['dns']['status'] = 'OK';
                        $info['dns']['ip'] = $matches[2];
                    }
                    unset($matches);
                    break;
                case ($line === 4);
                    preg_match('/HTTP.*response... (\d+)\s([\w\s]+)/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $info['response']['code'] = (int) $matches[1];
                    }
                    if (isset($matches[2])) {
                        $info['response']['text'] = $matches[2];
                    }
                    unset($matches);
                    break;
                case ($line === 5);
                    preg_match('/Length:\s(\d+)\s.*\[(.+)\]/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $info['length'] = (int) $matches[1];
                        $info['size']['completed'] = (int) $matches[1];
                    }
                    if (isset($matches[2])) {
                        $info['mime'] = $matches[2];
                    }
                    unset($matches);
                    break;
                case ($line === 6);
                    preg_match('/Saving to: `(.+)\'/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $info['filename'] = $matches[1];
                    }
                    unset($matches);
                    break;
                case ($line > 9);
                    // end time
                    preg_match('/(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})/', $buffer, $matches);
                    if (isset($matches[1])) {
                        $date = new Zend_Date($matches[1]);
                        $info['timestamp']['end'] = (int) $date->getTimestamp();
                        unset($date);
                    }
                    unset($matches);
                    break;
            }
        }
        fclose($handle);
        return $info;
    }

    protected function getLogFile($id) {
        return $this->getLogDir() . '/' . $id . '.log';
    }

    public function getInfo($id = null) {

        if (is_null($id)) {
            if (is_null($this->getId())) {
                throw new \Exception('no id given or object has no id');
            }
            $id = $this->getId();
        }
        try {
            $info = $this->parseLog($this->getLogFile($id));
            if (array_key_exists('response', $info) && $info['response']['code'] == 200) {
                $filename = $this->getDownloadDir() . '/' . $info['filename'];
                if (file_exists($filename)) {
                    $info['size']['downloaded'] = (int) filesize($filename);
                    $info['progress'] = $info['size']['downloaded'] / $info['size']['completed']; 
                }
                if ($info['progress'] == 1) {
                    $info['status'] = 'FINISHED';
                } else {
                    $info['status'] = 'DOWNLOADING';
                }
            } else {
                $info['status'] = 'ERROR';
            }
            return $info;
        } catch (\Exception $e) {
            throw new \Exception('id does not exists: ' . $e->getMessage());
        }
    }

}
<?php

require_once(APPLICATION_PATH . '/models/File.php');

class Api_Model_Download extends Default_Model_File {

    protected static $_db = 'mediathek';
    protected static $_collection = 'downloads';

    const STATUS_NEW = 'STATUS_NEW';
    const STATUS_PROCESSING = 'STATUS_PROCESSING';
    const STATUS_FINISHED = 'STATUS_FINISHED';
    const STATUS_ARCHIVED = 'STATUS_ARCHIVED';
    const STATUS_ERROR = 'STATUS_ERROR';

    /**
     * @property string $tempfile
     * @property string $status
     * @property string $url
     * @property string $error
     * @property array $datetime
     */
    protected static $_requirements = array(
//        'url' => array('Required', 'Validator:Regex' => '(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?'),
        'url' => array('Required'),
    );

    public function __construct($data = array(), $config = array()) {
        parent::__construct($data, $config);
    }
    
    
    public function preInsert() {
        $this->created = time();
        $this->status = self::STATUS_NEW;
        parent::preInsert();
    }
    
    public function preUpdate() {
        $this->modified = time();
        parent::preUpdate();
    }

    public function setStatus($status) {
        print_r($status);
        if (is_integer($status)) {
            $this->status = $status;
            return;
        }
        if (is_string($status)) {
            $this->status = $this->getStatusValueByName($status);
            return;
        }
    }

    public function getStatus($asString = false) {
        if ($asString) {
            return $this->getStatusNameByValue($this->status);
        }
        return $this->status;
    }

    public function getStatusNameByValue($value) {
        $arr = array_flip($this->getStatusValues());
        if (!array_key_exists($value, $arr)) {
            throw new \Exception('No name for status value = ' . $value);
        }
        return $arr[$value];
    }

    public function getStatusValueByName($name) {
        $arr = array_flip($this->getStatusValues());
        if (!array_key_exists($name, $arr)) {
            throw new \Exception('Status name "' . $name .'" is not valid.');
        }
        return $arr[$name];
    }

    public function getStatusValues() {
        $refl = new ReflectionClass('Api_Model_Download');
        return $refl->getConstants();
    }

    public function __toArray($obj = null) {
        $arr = array();
        if (is_null($obj)) {
            $obj = $this;
        }
        foreach ($obj->getPropertyKeys() as $key) {
            $property = $obj->getProperty($key);
            if ($property instanceof Shanty_Mongo_Document) {
                $property = $this->__toArray($property);
            }
            switch ($key) {
                case '_id':
                    $arr[$key] = (string) $property;
                    break;
                case '_type':
                    // do nothing
                    break;
                case 'status':
                    $arr[$key] = $this->getStatus(true);
                    break;
                default:
                    $arr[$key] = $property;
            }
        }
        return $arr;
    }

}
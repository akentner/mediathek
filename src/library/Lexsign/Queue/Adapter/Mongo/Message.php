<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Queue.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

/**
 * @see Zend_Queue_Adapter_Db_Message
 */
require_once 'Shanty/Mongo.php';

/**
 * @see Zend_Queue_Adapter_Db_Message
 */
require_once 'Shanty/Mongo/Document.php';


/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Lexsign_Queue_Adapter_Mongo_Message extends Shanty_Mongo_Document
{
    protected static $_db = 'mediathek';
    protected static $_collection = 'media';
    
    
}

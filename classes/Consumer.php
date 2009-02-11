<?php
/**
 * Table Definition for consumer
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Consumer extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'consumer';                        // table name
    public $consumer_key;                    // string(255)  not_null primary_key binary
    public $seed;                            // string(32)  not_null binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Consumer',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

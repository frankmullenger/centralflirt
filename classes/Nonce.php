<?php
/**
 * Table Definition for nonce
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Nonce extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'nonce';                           // table name
    public $consumer_key;                    // string(255)  not_null primary_key binary
    public $tok;                             // string(32)  not_null primary_key binary
    public $nonce;                           // string(32)  not_null primary_key binary
    public $ts;                              // datetime(19)  not_null binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Nonce',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

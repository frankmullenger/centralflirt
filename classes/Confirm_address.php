<?php
/**
 * Table Definition for confirm_address
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Confirm_address extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'confirm_address';                 // table name
    public $code;                            // string(32)  not_null primary_key binary
    public $user_id;                         // int(11)  not_null
    public $address;                         // string(255)  not_null binary
    public $address_extra;                   // string(255)  not_null binary
    public $address_type;                    // string(8)  not_null binary
    public $claimed;                         // datetime(19)  binary
    public $sent;                            // datetime(19)  binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Confirm_address',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function sequenceKey()
    { return array(false, false); }
}

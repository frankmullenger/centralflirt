<?php
/**
 * Table Definition for invitation
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Invitation extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'invitation';                      // table name
    public $code;                            // string(32)  not_null primary_key binary
    public $user_id;                         // int(11)  not_null multiple_key
    public $address;                         // string(255)  not_null multiple_key binary
    public $address_type;                    // string(8)  not_null binary
    public $created;                         // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Invitation',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

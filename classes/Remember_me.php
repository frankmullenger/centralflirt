<?php
/**
 * Table Definition for remember_me
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Remember_me extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'remember_me';                     // table name
    public $code;                            // string(32)  not_null primary_key binary
    public $user_id;                         // int(11)  not_null
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Remember_me',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function sequenceKey()
    { return array(false, false); }
}

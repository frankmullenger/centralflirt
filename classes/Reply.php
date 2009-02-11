<?php
/**
 * Table Definition for reply
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Reply extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'reply';                           // table name
    public $notice_id;                       // int(11)  not_null primary_key multiple_key
    public $profile_id;                      // int(11)  not_null primary_key multiple_key
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $replied_id;                      // int(11)  multiple_key

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Reply',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

<?php
/**
 * Table Definition for notice_source
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Notice_source extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'notice_source';                   // table name
    public $code;                            // string(32)  not_null primary_key binary
    public $name;                            // string(255)  not_null binary
    public $url;                             // string(255)  not_null binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Notice_source',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

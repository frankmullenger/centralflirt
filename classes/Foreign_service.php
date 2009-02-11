<?php
/**
 * Table Definition for foreign_service
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Foreign_service extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'foreign_service';                 // table name
    public $id;                              // int(11)  not_null primary_key
    public $name;                            // string(32)  not_null unique_key binary
    public $description;                     // string(255)  binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Foreign_service',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

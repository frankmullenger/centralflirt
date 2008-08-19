<?php
/**
 * Table Definition for foreign_user
 */
require_once 'DB/DataObject.php';

class Foreign_user extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'foreign_user';                    // table name
    public $id;                              // int(4)  primary_key not_null
    public $service;                         // int(4)  primary_key not_null
    public $uri;                             // varchar(255)  unique_key not_null
    public $nickname;                        // varchar(255)  
    public $user_id;                         // int(4)  
    public $credentials;                     // varchar(255)  
    public $created;                         // datetime()   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Foreign_user',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

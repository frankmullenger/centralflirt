<?php
/**
 * Table Definition for sms_carrier
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Sms_carrier extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sms_carrier';                     // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $name;                            // string(64)  unique_key binary
    public $email_pattern;                   // string(255)  not_null binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sms_carrier',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function toEmailAddress($sms)
    {
        return sprintf($this->email_pattern, $sms);
    }
}

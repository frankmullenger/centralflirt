<?php
if (!defined('LACONICA')) { exit(1); }

/**
 * Table Definition for pending_subscription
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Pending_subscription extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'pending_subscription';            // table name
    public $subscriber;                      // int(11)  not_null primary_key multiple_key
    public $subscribed;                      // int(11)  not_null primary_key multiple_key
    public $jabber;                          // int(4)  
    public $sms;                             // int(4)  
    public $token;                           // string(255)  multiple_key binary
    public $secret;                          // string(255)  binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Pending_subscription',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    function &pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGet('Pending_subscription', $kv);
    }
}

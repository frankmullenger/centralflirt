<?php
/**
 * Table Definition for foreign_subscription
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Foreign_subscription extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'foreign_subscription';            // table name
    public $service;                         // int(11)  not_null primary_key
    public $subscriber;                      // int(11)  not_null primary_key multiple_key
    public $subscribed;                      // int(11)  not_null primary_key multiple_key
    public $created;                         // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Foreign_subscription',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

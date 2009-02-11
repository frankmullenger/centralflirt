<?php
/**
 * Table Definition for group_inbox
 */

class Group_inbox extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'group_inbox';                     // table name
    public $group_id;                        // int(11)  not_null primary_key
    public $notice_id;                       // int(11)  not_null primary_key
    public $created;                         // datetime(19)  not_null multiple_key binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Group_inbox',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

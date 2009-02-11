<?php
/**
 * Table Definition for related_group
 */

class Related_group extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'related_group';                   // table name
    public $group_id;                        // int(11)  not_null primary_key
    public $related_group_id;                // int(11)  not_null primary_key
    public $created;                         // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Related_group',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

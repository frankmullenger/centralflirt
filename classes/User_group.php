<?php
/**
 * Table Definition for user_group
 */
require_once 'classes/Memcached_DataObject.php';

class User_group extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_group';                      // table name
    public $id;                              // int(4)  primary_key not_null
    public $nickname;                        // varchar(64)  unique_key
    public $fullname;                        // varchar(255)
    public $homepage;                        // varchar(255)
    public $description;                     // varchar(140)
    public $location;                        // varchar(255)
    public $original_logo;                   // varchar(255)
    public $homepage_logo;                   // varchar(255)
    public $stream_logo;                     // varchar(255)
    public $mini_logo;                       // varchar(255)
    public $created;                         // datetime()   not_null
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* Static get */
    function staticGet($k,$v=NULL) { return Memcached_DataObject::staticGet('User_group',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function defaultLogo($size) {
        static $sizenames = array(AVATAR_PROFILE_SIZE => 'profile',
                                  AVATAR_STREAM_SIZE => 'stream',
                                  AVATAR_MINI_SIZE => 'mini');
        return theme_path('default-avatar-'.$sizenames[$size].'.png');
    }

    function homeUrl() {
        return common_local_url('showgroup',
                                array('nickname' => $this->nickname));
    }

    function permalink() {
        return common_local_url('groupbyid',
                                array('id' => $this->id));
    }

    function getNotices($offset, $limit) {
        $qry =
          'SELECT notice.* ' .
          'FROM notice JOIN group_inbox ON notice.id = group_inbox.notice_id ' .
          'WHERE group_inbox.group_id = %d ';
        return Notice::getStream(sprintf($qry, $this->id),
                                 'group:notices:'.$this->id,
                                 $offset, $limit);
    }
}

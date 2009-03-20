<?php
/**
 * Table Definition for user_group
 */

class User_group extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_group';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $nickname;                        // string(64)  multiple_key binary
    public $fullname;                        // string(255)  binary
    public $homepage;                        // string(255)  binary
    public $description;                     // string(140)  binary
    public $location;                        // string(255)  binary
    public $original_logo;                   // string(255)  binary
    public $homepage_logo;                   // string(255)  binary
    public $stream_logo;                     // string(255)  binary
    public $mini_logo;                       // string(255)  binary
    public $is_private;                      // int(1)  not_null
    public $admin_nickname;                  // string(64)  binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User_group',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function defaultLogo($size)
    {
        static $sizenames = array(AVATAR_PROFILE_SIZE => 'profile',
                                  AVATAR_STREAM_SIZE => 'stream',
                                  AVATAR_MINI_SIZE => 'mini');
        return theme_path('default-avatar-'.$sizenames[$size].'.png');
    }

    function homeUrl()
    {
        //If private group pass admin nickname as usernick in home url
        if (common_config('profile', 'enable_dating') && $this->is_private) {
            
            /*
             * TODO frank: is it bad to use admin nickname from the db here? perhpas should use
             * $cur = common_current_user();
             * if ($cur && $cur->isAdmin($this->group)) {}
             * to avoid hackers masquerading and getting access to groups without session cookies.
             */
            
            return common_local_url('showgroup',
                                array('nickname' => $this->nickname, 'usernick' => $this->admin_nickname));
        }
        
        return common_local_url('showgroup',
                                array('nickname' => $this->nickname));
    }

    function permalink()
    {
        return common_local_url('groupbyid',
                                array('id' => $this->id));
    }

    function getNotices($offset, $limit)
    {
        $qry =
          'SELECT notice.* ' .
          'FROM notice JOIN group_inbox ON notice.id = group_inbox.notice_id ' .
          'WHERE group_inbox.group_id = %d ';
        return Notice::getStream(sprintf($qry, $this->id),
                                 'group:notices:'.$this->id,
                                 $offset, $limit);
    }

    function allowedNickname($nickname)
    {
        static $blacklist = array('new', 'public');
        return !in_array($nickname, $blacklist);
    }

    function getMembers($offset=0, $limit=null)
    {
        $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN group_member '.
          'ON profile.id = group_member.profile_id ' .
          'WHERE group_member.group_id = %d ';
        
        if ($this->is_private) {
            $qry .= sprintf("AND profile.nickname <> '%s' ", $this->admin_nickname);
        }
        $qry .= 'ORDER BY group_member.created DESC ';

        if ($limit != null) {
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        $members = new Profile();

        $members->query(sprintf($qry, $this->id));
        return $members;
    }
    
    public function getNonMembers($cur, $offset=0, $limit=null) {
        
        /*
         * TODO frank: if cur does not match the current user then throw an exception
         */
        
        if (common_config('profile', 'enable_dating')) {
            if ($this->is_private) {
                
                /*
                 * Select profiles subscribed to current user that are not members of this group
                 */
                $curId = $cur->id;
                $groupId = $this->id;
                $qry = <<<EOS
SELECT p.*
FROM profile p
INNER JOIN subscription s ON s.subscriber = p.id
WHERE s.subscribed = %d
AND p.id NOT IN (SELECT gm.profile_id FROM group_member gm WHERE gm.group_id = %d)
ORDER BY p.nickname ASC
EOS;

                if ($limit != null) {
                    if (common_config('db','type') == 'pgsql') {
                        $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
                    } else {
                        $qry .= ' LIMIT ' . $offset . ', ' . $limit;
                    }
                }
        
                $members = new Profile();
        
                $members->query(sprintf($qry, $cur->id, $this->id));
                return $members;
                
            }
        }
    }

    function setOriginal($filename)
    {
        $imagefile = new ImageFile($this->id, Avatar::path($filename));
        
        $orig = clone($this);
        $this->original_logo = Avatar::url($filename);
        $this->homepage_logo = Avatar::url($imagefile->resize(AVATAR_PROFILE_SIZE));
        $this->stream_logo = Avatar::url($imagefile->resize(AVATAR_STREAM_SIZE));
        $this->mini_logo = Avatar::url($imagefile->resize(AVATAR_MINI_SIZE));
        common_debug(common_log_objstring($this));
        return $this->update($orig);
    }
}

<?php
/**
 * Table Definition for avatar
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Avatar extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'avatar';                          // table name
    public $profile_id;                      // int(11)  not_null primary_key multiple_key
    public $original;                        // int(1)  
    public $width;                           // int(11)  not_null primary_key
    public $height;                          // int(11)  not_null primary_key
    public $mediatype;                       // string(32)  not_null binary
    public $filename;                        // string(255)  binary
    public $url;                             // string(255)  unique_key binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Avatar',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    # We clean up the file, too

    function delete()
    {
        $filename = $this->filename;
        if (parent::delete()) {
            @unlink(Avatar::path($filename));
        }
    }

    function &pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGet('Avatar', $kv);
    }

    // where should the avatar go for this user?

    static function filename($id, $extension, $size=null, $extra=null)
    {
        if ($size) {
            return $id . '-' . $size . (($extra) ? ('-' . $extra) : '') . $extension;
        } else {
            return $id . '-original' . (($extra) ? ('-' . $extra) : '') . $extension;
        }
    }

    static function path($filename)
    {
        return INSTALLDIR . '/avatar/' . $filename;
    }

    static function url($filename)
    {
        return common_path('avatar/'.$filename);
    }

    function displayUrl()
    {
        $server = common_config('avatar', 'server');
        if ($server) {
            return 'http://'.$server.'/'.$this->filename;
        } else {
            return $this->url;
        }
    }

    static function defaultImage($size)
    {
        static $sizenames = array(AVATAR_PROFILE_SIZE => 'profile',
                                  AVATAR_STREAM_SIZE => 'stream',
                                  AVATAR_MINI_SIZE => 'mini');
        return theme_path('default-avatar-'.$sizenames[$size].'.png');
    }
}

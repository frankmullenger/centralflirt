<?php
/*
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, Controlez-Vous, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

/**
 * Table Definition for profile
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Profile extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'profile';                         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $nickname;                        // string(64)  not_null multiple_key binary
    public $fullname;                        // string(255)  binary
    public $profileurl;                      // string(255)  binary
    public $homepage;                        // string(255)  binary
    public $bio;                             // string(140)  binary
    public $location;                        // string(255)  binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Profile',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function getDatingProfile() 
    {
        //Check that the config setting for dating profiles is enabled before attempting to retrieve dating profiles
        if (common_config('profile', 'enable_dating')) {
            
            //If a dating profile has not been created yet then return an empty one
            $datingProfile = Dating_profile::staticGet('id', $this->id);
            if (!$datingProfile instanceof Dating_profile) {
                $datingProfile = new Dating_profile;
            }
            return $datingProfile;
        }
        else {
            return false;
        }
    }
    
    function getAvatar($width, $height=null)
    {
        if (is_null($height)) {
            $height = $width;
        }
        return Avatar::pkeyGet(array('profile_id' => $this->id,
                                     'width' => $width,
                                     'height' => $height));
    }

    function getOriginalAvatar()
    {
        $avatar = DB_DataObject::factory('avatar');
        $avatar->profile_id = $this->id;
        $avatar->original = true;
        if ($avatar->find(true)) {
            return $avatar;
        } else {
            return null;
        }
    }

    function setOriginal($filename)
    {
        $imagefile = new ImageFile($this->id, Avatar::path($filename));

        $avatar = new Avatar();
        $avatar->profile_id = $this->id;
        $avatar->width = $imagefile->width;
        $avatar->height = $imagefile->height;
        $avatar->mediatype = image_type_to_mime_type($imagefile->type);
        $avatar->filename = $filename;
        $avatar->original = true;
        $avatar->url = Avatar::url($filename);
        $avatar->created = DB_DataObject_Cast::dateTime(); # current time

        # XXX: start a transaction here

        if (!$this->delete_avatars() || !$avatar->insert()) {
            @unlink(Avatar::path($filename));
            return null;
        }

        foreach (array(AVATAR_PROFILE_SIZE, AVATAR_STREAM_SIZE, AVATAR_MINI_SIZE) as $size) {
            # We don't do a scaled one if original is our scaled size
            if (!($avatar->width == $size && $avatar->height == $size)) {

                $scaled_filename = $imagefile->resize($size);

                //$scaled = DB_DataObject::factory('avatar');
                $scaled = new Avatar();
                $scaled->profile_id = $this->id;
                $scaled->width = $size;
                $scaled->height = $size;
                $scaled->original = false;
                $scaled->mediatype = image_type_to_mime_type($imagefile->type);
                $scaled->filename = $scaled_filename;
                $scaled->url = Avatar::url($scaled_filename);
                $scaled->created = DB_DataObject_Cast::dateTime(); # current time

                if (!$scaled->insert()) {
                    return null;
                }
            }
        }

        return $avatar;
    }

    function delete_avatars($original=true)
    {
        $avatar = new Avatar();
        $avatar->profile_id = $this->id;
        $avatar->find();
        while ($avatar->fetch()) {
            if ($avatar->original) {
                if ($original == false) {
                    continue;
                }
            }
            $avatar->delete();
        }
        return true;
    }

    function getBestName()
    {
        return ($this->fullname) ? $this->fullname : $this->nickname;
    }

    # Get latest notice on or before date; default now
    function getCurrentNotice($dt=null)
    {
        $notice = new Notice();
        $notice->profile_id = $this->id;
        if ($dt) {
            $notice->whereAdd('created < "' . $dt . '"');
        }
        $notice->orderBy('created DESC, notice.id DESC');
        $notice->limit(1);
        if ($notice->find(true)) {
            return $notice;
        }
        return null;
    }

    /**
     * Get notices for this profile. A user must be logged in to access notices.
     * If the current logged in user is not the owner of the profile then filter the messages 
     * to only those that the logged in user can view.
     *
     * @param unknown_type $offset
     * @param unknown_type $limit
     * @param unknown_type $since_id
     * @param unknown_type $before_id
     * @return unknown
     */
    function getNotices($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0)
    {
        if (common_config('profile', 'enable_dating')) {
            $cur = common_current_user();
            
            //Belt and braces - this is checked in action files.
            if (!$cur) {
                $this->clientError(_('Only logged in users can access notices.'),403);
                return;
            }

            if ($cur->id !== $this->id) {

                $enabled = common_config('inboxes', 'enabled');
    
                //TODO frank: this is not going to work if inboxes are disabled, so that can never be the case on dating enabled sites
                
                # Complicated code, depending on whether we support inboxes yet
                # XXX: make this go away when inboxes become mandatory
        
                if ($enabled === false ||
                    ($enabled == 'transitional' && $cur->inboxed == 0)) {
                        
                    $qry =
                      'SELECT notice.* ' .
                      'FROM notice JOIN subscription ON notice.profile_id = subscription.subscribed ' .
                      'WHERE subscription.subscriber = %d ' . 
                      'AND notice.profile_id = %d';
                    $order = null;
                } else if ($enabled === true ||
                       ($enabled == 'transitional' && $cur->inboxed == 1)) {
                           
                    $qry =
                      'SELECT notice.* ' .
                      'FROM notice JOIN notice_inbox ON notice.id = notice_inbox.notice_id ' .
                      'WHERE notice_inbox.user_id = %d ' . 
                      'AND notice.profile_id = %d';
                    # NOTE: we override ORDER
                    $order = null;
                }
                return Notice::getStream(sprintf($qry, $cur->id, $this->id),
                                     'profile:notices:'.$this->id,
                                     $offset, $limit, $since_id, $before_id);
            }
        }
    
        $qry =
          'SELECT * ' .
          'FROM notice ' .
          'WHERE profile_id = %d ';

        return Notice::getStream(sprintf($qry, $this->id),
                                 'profile:notices:'.$this->id,
                                 $offset, $limit, $since_id, $before_id);
    }

    function isMember($group)
    {
        $mem = new Group_member();

        $mem->group_id = $group->id;
        $mem->profile_id = $this->id;

        if ($mem->find()) {
            return true;
        } else {
            return false;
        }
    }

    function isAdmin($group)
    {
        $mem = new Group_member();

        $mem->group_id = $group->id;
        $mem->profile_id = $this->id;
        $mem->is_admin = 1;

        if ($mem->find()) {
            return true;
        } else {
            return false;
        }
    }

    function avatarUrl($size=AVATAR_PROFILE_SIZE)
    {
        $avatar = $this->getAvatar($size);
        if ($avatar) {
            return $avatar->displayUrl();
        } else {
            return Avatar::defaultImage($size);
        }
    }
}

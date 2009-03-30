<?php
/**
 * Table Definition for dating_profile_tag
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Dating_profile_tag extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dating_profile_tag';              // table name
    public $tagger;                          // int(11)  not_null primary_key multiple_key
    public $tagged;                          // int(11)  not_null primary_key
    public $tag;                             // string(64)  not_null primary_key binary
    public $modified;                        // timestamp(19)  not_null multiple_key unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Dating_profile_tag',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    static function setTags($tagger, $tagged, $newtags) {
        
        $oldtags = Dating_profile_tag::getTags($tagger, $tagged);
        
        # Delete stuff that's old that not in new
        
        $to_delete = array_diff($oldtags, $newtags);
        
        # Insert stuff that's in new and not in old
        
        $to_insert = array_diff($newtags, $oldtags);
        
        $profile_tag = new Dating_profile_tag();
        
        $profile_tag->tagger = $tagger;
        $profile_tag->tagged = $tagged;
        
        $profile_tag->query('BEGIN');
        
        foreach ($to_delete as $deltag) {
            $profile_tag->tag = $deltag;
            $result = $profile_tag->delete();
            if (!$result) {
                common_log_db_error($profile_tag, 'DELETE', __FILE__);
                return false;
            }
        }
        
        foreach ($to_insert as $instag) {
            $profile_tag->tag = $instag;
            $result = $profile_tag->insert();
            if (!$result) {
                common_log_db_error($profile_tag, 'INSERT', __FILE__);
                return false;
            }
        }
        
        $profile_tag->query('COMMIT');
        
        return true;
    }
    
    static function getTags($tagger, $tagged) {
        
        $tags = array();

        # XXX: store this in memcached
        
        $profile_tag = new Dating_profile_tag();
        $profile_tag->tagger = $tagger;
        $profile_tag->tagged = $tagged;
        
        $profile_tag->find();
        
        while ($profile_tag->fetch()) {
            $tags[] = $profile_tag->tag;
        }
        
        $profile_tag->free();
        
        return $tags;
    }
    
    static function getTagged($tagger, $tag) {
        $profile = new Dating_profile();
        $profile->query('SELECT dating_profile.* ' .
                        'FROM dating_profile JOIN dating_profile_tag ' .
                        'ON dating_profile.id = dating_profile_tag.tagged ' .
                        'WHERE dating_profile_tag.tagger = ' . $tagger . ' ' .
                        'AND dating_profile_tag.tag = "' . $tag . '" ');
        $tagged = array();
        while ($profile->fetch()) {
            $tagged[] = clone($profile);
        }
        return $tagged;
    }
}

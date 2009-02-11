<?php
/**
 * Table Definition for foreign_user
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Foreign_user extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'foreign_user';                    // table name
    public $id;                              // int(11)  not_null primary_key
    public $service;                         // int(11)  not_null primary_key
    public $uri;                             // string(255)  not_null unique_key binary
    public $nickname;                        // string(255)  binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Foreign_user',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    // XXX:  This only returns a 1->1 single obj mapping.  Change?  Or make
    // a getForeignUsers() that returns more than one? --Zach
    static function getForeignUser($id, $service) {        
        $fuser = new Foreign_user();
        $fuser->whereAdd("service = $service");
        $fuser->whereAdd("id = $id");
        $fuser->limit(1);
        
        if ($fuser->find()) {
            $fuser->fetch();
            return $fuser;
        }
        
        return null;        
    }
    
    function updateKeys(&$orig)
    {
        $parts = array();
        foreach (array('id', 'service', 'uri', 'nickname') as $k) {
            if (strcmp($this->$k, $orig->$k) != 0) {
                $parts[] = $k . ' = ' . $this->_quote($this->$k);
            }
        }
        if (count($parts) == 0) {
            # No changes
            return true;
        }
        $toupdate = implode(', ', $parts);

        $table = $this->tableName();
        if(common_config('db','quote_identifiers')) {
            $table = '"' . $table . '"';
        }
        $qry = 'UPDATE ' . $table . ' SET ' . $toupdate .
          ' WHERE id = ' . $this->id;
        $orig->decache();
        $result = $this->query($qry);
        if ($result) {
            $this->encache();
        }
        return $result;
    }

    
}

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

require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Memcached_DataObject extends DB_DataObject 
{
    function &staticGet($cls, $k, $v=NULL) {
		if (is_null($v)) {
			$v = $k;
			# XXX: HACK!
			$i = new $cls;
			$keys = $i->keys();
			$k = $keys[0];
			unset($i);
		}
		$i = Memcached_DataObject::getcached($cls, $k, $v);
		if ($i) {
			return $i;
		} else {
			$i = DB_DataObject::staticGet($cls, $k, $v);
			if ($i) {
				$i->encache();
			}
			return $i;
		}
	}
	
	function insert() {
		$result = parent::insert();
		if ($result) {
			$this->encache();
		}
		return $result;
	}
	
	function update($orig=NULL) {
		if (is_object($orig) && $orig instanceof Memcached_DataObject) {
			$orig->decache(); # might be different keys
		}
		$result = parent::update($orig);
		if ($result) {
			$this->encache();
		}
		return $result;
	}
	
	function delete() {
		$this->decache(); # while we still have the values!
		return parent::delete();
	}
	
	static function memcache() {
		return common_memcache();
	}
	
	static function cacheKey($cls, $k, $v) {
		return common_cache_key(strtolower($cls).':'.$k.':'.$v);
	}
	
	static function getcached($cls, $k, $v) {
		$c = Memcached_DataObject::memcache();
		if (!$c) {
			return false;
		} else {
			return $c->get(Memcached_DataObject::cacheKey($cls, $k, $v));
		}
	}

	function keyTypes() {
		global $_DB_DATAOBJECT;
        if (!isset($_DB_DATAOBJECT['INI'][$this->_database][$this->__table."__keys"])) {
			$this->databaseStructure();

        }
		return $_DB_DATAOBJECT['INI'][$this->_database][$this->__table."__keys"];
	}
	
	function encache() {
		$c = $this->memcache();
		if (!$c) {
			return false;
		} else {
			$primary = array();
			$types = $this->keyTypes();
			ksort($types);
			foreach ($types as $key => $type) {
				if ($type == 'K') {
					$primary[] = $key;
				} else {
					$v = $this->$key;
					if (!is_null($v)) {
						$c->set($this->cacheKey($this->tableName(), $key, $v),
								$this);
					}
				}
			}
			# XXX: figure out what to do with compound pkeys
			if (count($primary) == 1) {
				$key = $primary[0];
				$c->set($this->cacheKey($this->tableName(), $key, $this->$key),
						$this);
			}
		}
	}
	
	function decache() {
		$c = $this->memcache();
		if (!$c) {
			return false;
		} else {
			$primary = array();
			$types = $this->keyTypes();
			ksort($types);
			foreach ($types as $key => $type) {
				if ($type == 'K') {
					$primary[] = $this->$key;
				} else {
					$c->delete($this->cacheKey($this->tableName(), $key, $this->$key));
				}
			}
			# XXX: figure out what to do with compound pkeys
			if (count($primary) == 1) {
				$key = $primary[0];
				$c->delete($this->cacheKey($this->tableName(), $key, $this->$key));
			}
		}
	}
}

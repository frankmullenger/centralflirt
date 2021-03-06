<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Groups with the most posts section
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 *
 * @category  Widget
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

/**
 * Groups with the most posts section
 *
 * @category Widget
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class GroupsByPostsSection extends GroupSection
{
    function getGroups()
    {
        $qry = 'SELECT user_group.*, count(*) as value ' .
          'FROM user_group JOIN group_inbox '.
          'ON user_group.id = group_inbox.group_id ' .
          'WHERE user_group.is_private = 0 ' .
          'GROUP BY user_group.id ' .
          'ORDER BY value DESC ';

        if ($this->out->privateGroups) {
            $cur = common_current_user();
            $qry = <<<EOS
SELECT user_group.*, count(*) as value 
FROM user_group JOIN group_inbox 
ON user_group.id = group_inbox.group_id 
WHERE user_group.admin_nickname = '%s' 
GROUP BY user_group.id 
ORDER BY value DESC 
EOS;
            $qry = sprintf($qry, $cur->nickname);
        }
        
        $limit = GROUPS_PER_SECTION;
        $offset = 0;

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $group = Memcached_DataObject::cachedQuery('User_group',
                                                   $qry,
                                                   3600);
        return $group;
    }

    function title()
    {
        if ($this->out->privateGroups) {
            return _('Your groups with most posts');
        }
        return _('Groups with most posts');
    }

    function divId()
    {
        return 'top_groups_by_post';
    }
}

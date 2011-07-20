<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\UserGroupList;

/**
 * Caches all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderUserGroup implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('types' => array(), 'groups' => array());

		// get all user groups
		$groupList = new UserGroupList();
		$groupList->sqlOrderBy = "user_group.groupName";
		$groupList->sqlLimit = 0;
		$groupList->readObjects();
		$groups = $groupList->getObjects();
		
		foreach ($groups as $group) {
			if (!isset($data['types'][$group->groupType])) {
				$data['types'][$group->groupType] = array();
			}
			
			$data['types'][$group->groupType][] = $group->groupID;
			$data['groups'][$group->groupID] = $group;
		}
		
		return $data;
	}
}

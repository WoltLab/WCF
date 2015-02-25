<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\UserGroupList;

/**
 * Caches all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserGroupCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array(
			'types' => array(),
			'groups' => array()
		);
		
		// get all user groups
		$groupList = new UserGroupList();
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

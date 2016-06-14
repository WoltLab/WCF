<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\UserGroupList;

/**
 * Caches all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class UserGroupCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'types' => [],
			'groups' => []
		];
		
		// get all user groups
		$groupList = new UserGroupList();
		$groupList->readObjects();
		$groups = $groupList->getObjects();
		
		foreach ($groups as $group) {
			if (!isset($data['types'][$group->groupType])) {
				$data['types'][$group->groupType] = [];
			}
			
			$data['types'][$group->groupType][] = $group->groupID;
			$data['groups'][$group->groupID] = $group;
		}
		
		return $data;
	}
}

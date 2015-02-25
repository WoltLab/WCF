<?php
namespace wcf\system\cache\builder;
use wcf\data\label\group\LabelGroupList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\LabelList;
use wcf\system\acl\ACLHandler;

/**
 * Caches labels and groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class LabelCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$data = array(
			'options' => array(),
			'groups' => array()
		);
		
		// get label groups
		$groupList = new LabelGroupList();
		$groupList->readObjects();
		$groups = $groupList->getObjects();
		foreach ($groups as &$group) {
			$data['groups'][$group->groupID] = new ViewableLabelGroup($group);
		}
		unset($group);
		
		// get permissions for groups
		$permissions = ACLHandler::getInstance()->getPermissions(
			ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label'),
			array_keys($data['groups'])
		);
		
		// store options
		$data['options'] = $permissions['options']->getObjects();
		
		// assign permissions for each label group
		foreach ($data['groups'] as $groupID => $group) {
			// group permissions
			if (isset($permissions['group'][$groupID])) {
				$group->setGroupPermissions($permissions['group'][$groupID]);
			}
			
			// user permissions
			if (isset($permissions['user'][$groupID])) {
				$group->setUserPermissions($permissions['user'][$groupID]);
			}
		}
		
		if (count($groupList)) {
			// get labels
			$labelList = new LabelList();
			$labelList->sqlOrderBy = 'label';
			$labelList->readObjects();
			foreach ($labelList as $label) {
				$data['groups'][$label->groupID]->addLabel($label);
			}
		}
		
		return $data;
	}
}

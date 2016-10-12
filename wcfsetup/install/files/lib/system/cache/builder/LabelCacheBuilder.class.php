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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class LabelCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [
			'options' => [],
			'groups' => []
		];
		
		// get label groups
		$groupList = new LabelGroupList();
		$groupList->decoratorClassName = ViewableLabelGroup::class;
		$groupList->readObjects();
		$data['groups'] = $groupList->getObjects();
		
		// get permissions for groups
		$permissions = ACLHandler::getInstance()->getPermissions(
			ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label'),
			array_keys($data['groups'])
		);
		
		// store options
		/** @noinspection PhpUndefinedMethodInspection */
		$data['options'] = $permissions['options']->getObjects();
		
		// assign permissions for each label group
		/** @var ViewableLabelGroup $group */
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
			$labelList->readObjects();
			foreach ($labelList as $label) {
				/** @noinspection PhpUndefinedMethodInspection */
				$data['groups'][$label->groupID]->addLabel($label);
			}
		}
		
		return $data;
	}
}

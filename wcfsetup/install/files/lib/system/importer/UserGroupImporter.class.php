<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;

/**
 * Imports user groups.
 * 
 * @author	Alexander Ebert, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserGroupImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if ($data['groupType'] < 4) {
			$newGroupID = UserGroup::getGroupByType($data['groupType'])->groupID;
		}
		else {
			// Imported owner groups must be degraded, there can be only one owner group.
			if ($data['groupType'] == UserGroup::OWNER) {
				$data['groupType'] = UserGroup::OTHER;
			}
			
			$action = new UserGroupAction([], 'create', [
				'data' => $data
			]);
			$returnValues = $action->executeAction();
			$group = $returnValues['returnValues'];
			$newGroupID = $group->groupID;
			
			// handle i18n values
			if (!empty($additionalData['i18n'])) {
				$values = [];
				
				foreach (['groupName', 'groupDescription'] as $property) {
					if (isset($additionalData['i18n'][$property])) {
						$values[$property] = $additionalData['i18n'][$property];
					}
				}
				
				if (!empty($values)) {
					$updateData = [];
					if (isset($values['groupName'])) $updateData['groupName'] = 'wcf.acp.group.group' . $newGroupID;
					if (isset($values['groupDescription'])) $updateData['groupDescription'] = 'wcf.acp.group.groupDescription' . $newGroupID;
					
					$items = [];
					foreach ($values as $property => $propertyValues) {
						foreach ($propertyValues as $languageID => $languageItemValue) {
							$items[] = [
								'languageID' => $languageID,
								'languageItem' => 'wcf.acp.group.' . ($property === 'description' ? 'groupDescription' : 'group') . $newGroupID,
								'languageItemValue' => $languageItemValue
							];
						}
					}
					
					$this->importI18nValues($items, 'wcf.acp.group', 'com.woltlab.wcf');
					
					(new UserGroupEditor($group))->update($updateData);
				}
			}
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.group', $oldID, $newGroupID);
		
		return $newGroupID;
	}
}

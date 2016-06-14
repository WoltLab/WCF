<?php
namespace wcf\system\package\plugin;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionEditor;
use wcf\data\user\group\UserGroup;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserGroupOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * list of group ids by type
	 * @var	integer[][]
	 */
	protected $groupIDs = null;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_group_option';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	string[]
	 */
	public static $reservedTags = ['name', 'optiontype', 'defaultvalue', 'admindefaultvalue', 'userdefaultvalue', 'moddefaultvalue', 'validationpattern', 'showorder', 'categoryname', 'selectoptions', 'enableoptions', 'permissions', 'options', 'attrs', 'cdata', 'usersonly'];
	
	/**
	 * @inheritDoc
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $adminDefaultValue = $modDefaultValue = $userDefaultValue = $validationPattern = $enableOptions = $permissions = $options = '';
		$usersOnly = 0;
		$showOrder = null;
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = $option['defaultvalue'];
		if (isset($option['admindefaultvalue'])) $adminDefaultValue = $option['admindefaultvalue'];
		if (isset($option['moddefaultvalue'])) $modDefaultValue = $option['moddefaultvalue'];
		if (isset($option['userdefaultvalue'])) $userDefaultValue = $option['userdefaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (!empty($option['showorder'])) $showOrder = intval($option['showorder']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		if (isset($option['usersonly'])) $usersOnly = $option['usersonly'];
		
		// collect additional tags and their values
		$additionalData = [];
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// check if the otion exist already and was installed by this package
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionName = ?
			AND	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$optionName,
			$this->installation->getPackageID()
		]);
		$row = $statement->fetchArray();
		
		$data = [
			'categoryName' => $categoryName,
			'optionType' => $optionType,
			'defaultValue' => (isset($option['userdefaultvalue']) ? $userDefaultValue : $defaultValue),
			'validationPattern' => $validationPattern,
			'showOrder' => $showOrder,
			'enableOptions' => $enableOptions,
			'permissions' => $permissions,
			'options' => $options,
			'usersOnly' => $usersOnly,
			'additionalData' => serialize($additionalData)
		];
		
		if (!empty($row['optionID'])) {
			$groupOption = new UserGroupOption(null, $row);
			$groupOptionEditor = new UserGroupOptionEditor($groupOption);
			$groupOptionEditor->update($data);
		}
		else {
			// add new option
			$data['packageID'] = $this->installation->getPackageID();
			$data['optionName'] = $optionName;
			
			$groupOptionEditor = UserGroupOptionEditor::create($data);
			$optionID = $groupOptionEditor->optionID;
			
			$this->getGroupIDs();
			$values = [];
			foreach ($this->groupIDs['all'] as $groupID) {
				$values[$groupID] = $defaultValue;
			}
			if (isset($option['userdefaultvalue'])) {
				foreach ($this->groupIDs['registered'] as $groupID) {
					$values[$groupID] = $userDefaultValue;
				}
			}
			if (isset($option['moddefaultvalue'])) {
				foreach ($this->groupIDs['mod'] as $groupID) {
					$values[$groupID] = $modDefaultValue;
				}
			}
			if (isset($option['admindefaultvalue'])) {
				foreach ($this->groupIDs['admin'] as $groupID) {
					$values[$groupID] = $adminDefaultValue;
				}
			}
			
			// save values
			$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
						(groupID, optionID, optionValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($values as $groupID => $value) {
				$statement->execute([
					$groupID,
					$optionID,
					$value
				]);
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Returns a list of group ids by type.
	 * 
	 * @return	integer[][]
	 */
	protected function getGroupIDs() {
		if ($this->groupIDs === null) {
			$this->groupIDs = [
				'admin' => [],
				'mod' => [],
				'all' => [],
				'registered' => []
			];
			
			$sql = "SELECT	groupID, groupType
				FROM	wcf".WCF_N."_user_group";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				$group = new UserGroup(null, $row);
				$this->groupIDs['all'][] = $group->groupID;
				
				if ($group->groupType != UserGroup::EVERYONE && $group->groupType != UserGroup::GUESTS) {
					$this->groupIDs['registered'][] = $group->groupID;
					
					if ($group->isModGroup()) {
						$this->groupIDs['mod'][] = $group->groupID;
					}
					if ($group->isAdminGroup()) {
						$this->groupIDs['admin'][] = $group->groupID;
					}
				}
			}
		}
		
		return $this->groupIDs;
	}
}

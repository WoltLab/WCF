<?php
namespace wcf\system\package\plugin;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionEditor;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupList;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class UserGroupOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * list of group ids by type
	 * @var	array<array>
	 */
	protected $groupIDs = null;
	
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'user_group_option';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	array<string>
	 */
	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'admindefaultvalue', 'userdefaultvalue', 'validationpattern', 'showorder', 'categoryname', 'selectoptions', 'enableoptions', 'permissions', 'options', 'attrs', 'cdata');
	
	/**
	 * @see	wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $adminDefaultValue = $userDefaultValue = $validationPattern = $enableOptions = $permissions = $options = '';
		$showOrder = null;
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = $option['defaultvalue'];
		if (isset($option['admindefaultvalue'])) $adminDefaultValue = $option['admindefaultvalue'];
		if (isset($option['userdefaultvalue'])) $userDefaultValue = $option['userdefaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (!empty($option['showorder'])) $showOrder = intval($option['showorder']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		
		// collect additional tags and their values
		$additionalData = array();
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// check if the otion exist already and was installed by this package
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionName = ?
			AND	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$optionName,
			$this->installation->getPackageID()
		));
		$row = $statement->fetchArray();
		
		$data = array(
			'categoryName' => $categoryName,
			'optionType' => $optionType,
			'defaultValue' => (isset($option['userdefaultvalue']) ? $userDefaultValue : $defaultValue),
			'validationPattern' => $validationPattern,
			'showOrder' => $showOrder,
			'enableOptions' => $enableOptions,
			'permissions' => $permissions,
			'options' => $options,
			'additionalData' => serialize($additionalData)
		);
		
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
			
			$groupIDs = $this->getGroupIDs();
			$values = array();
			foreach ($this->groupIDs['admin'] as $groupID) {
				$values[$groupID] = ((isset($option['admindefaultvalue']) && $defaultValue != $adminDefaultValue) ? $adminDefaultValue : $defaultValue);
			}
			foreach ($this->groupIDs['registered'] as $groupID) {
				$values[$groupID] = ((isset($option['userdefaultvalue']) && $defaultValue != $userDefaultValue) ? $userDefaultValue : $defaultValue);
			}
			foreach ($this->groupIDs['other'] as $groupID) {
				$values[$groupID] = $defaultValue;
			}
			
			// save values
			$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
						(groupID, optionID, optionValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($values as $groupID => $value) {
				$statement->execute(array(
					$groupID,
					$optionID,
					$value
				));
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Returns a list of group ids by type.
	 * 
	 * @return	array<array>
	 */
	protected function getGroupIDs() {
		if ($this->groupIDs === null) {
			$this->groupIDs = array(
				'admin' => array(),
				'other' => array(),
				'registered' => array()
			);
			
			$sql = "SELECT	groupID, groupType
				FROM	wcf".WCF_N."_user_group";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				$group = new UserGroup(null, $row);
				if ($group->groupType == UserGroup::EVERYONE || $group->groupType == UserGroup::GUESTS) {
					$this->groupIDs['other'][] = $group->groupID;
				}
				else {
					if ($group->isAdminGroup()) {
						$this->groupIDs['admin'][] = $group->groupID;
					}
					else {
						$this->groupIDs['registered'][] = $group->groupID;
					}
				}
			}
		}
		
		return $this->groupIDs;
	}
}

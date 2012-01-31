<?php
namespace wcf\system\package\plugin;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionEditor;
use wcf\system\WCF;
use wcf\system\exception\SystemException;
use wcf\util\StringUtil;

/**
 * This PIP installs, updates or deletes user group permissions.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class UserGroupOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */	
	public $tableName = 'user_group_option';

	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'admindefaultvalue', 'validationpattern', 'showorder', 'categoryname', 'selectoptions', 'enableoptions', 'permissions', 'options', 'attrs', 'cdata');
	
	/**
	 * Deletes group-option-categories and/or group-options which where installed by the package.
	 */
	public function uninstall() {
		// Delete value-entries using categories or options
		// which will be deleted.
		$sql = "DELETE FROM	wcf".WCF_N."_user_group_option_value
			WHERE		optionID IN (
						SELECT	optionID
						FROM	wcf".WCF_N."_user_group_option
						WHERE	packageID = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
			
		parent::uninstall();
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $adminDefaultValue = $validationPattern = $enableOptions = $permissions = $options = '';
		$showOrder = null;
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = $option['defaultvalue'];
		if (isset($option['admindefaultvalue'])) $adminDefaultValue = $option['admindefaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (!empty($option['showorder'])) $showOrder = intval($option['showorder']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		
		// check if optionType exists
		$className = 'wcf\system\option\user\group\\'.StringUtil::firstCharToUpperCase($optionType).'UserGroupOptionType';
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// collect additional tags and their values
		$additionalData = array();
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// check if the otion exist already and was installed by this package
		$sql = "SELECT	optionID
			FROM 	wcf".WCF_N."_user_group_option
			WHERE 	optionName = ?
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
			'defaultValue' => $defaultValue,
			'adminDefaultValue' => $adminDefaultValue,
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
			
			// get default group ("everyone")
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_user_group
				WHERE	groupType = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(UserGroup::EVERYONE));
			$row = $statement->fetchArray();
			
			// save default value
			$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
						(groupID, optionID, optionValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($row['groupID'], $optionID, $defaultValue));
			
			if ($adminDefaultValue && $defaultValue != $adminDefaultValue) {
				$sql = "SELECT	groupID
					FROM	wcf".WCF_N."_user_group_option_value
					WHERE	optionID = (
							SELECT	optionID
							FROM	wcf".WCF_N."_user_group_option
							WHERE	optionName = ?
						)
						AND optionValue = '1'";
				$statement2 = WCF::getDB()->prepareStatement($sql);
				$statement2->execute(array('admin.general.canUseAcp'));
				
				$acpGroups = array();
				while ($row = $statement2->fetchArray()) {
					$acpGroups[] = $row['groupID'];
				}
				
				$statement2->execute(array('admin.user.canEditGroup'));
				while ($row = $statement2->fetchArray()) {
					if (!in_array($row['groupID'], $acpGroups)) {
						continue;
					}
					
					$statement->execute(array(
						$row['groupID'],
						$optionID,
						$adminDefaultValue
					));
				}
			}
		}
	}
}

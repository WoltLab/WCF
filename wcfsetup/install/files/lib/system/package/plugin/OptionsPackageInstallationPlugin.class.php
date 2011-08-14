<?php
namespace wcf\system\package\plugin;
use wcf\data\option\Option;
use wcf\data\option\OptionEditor;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes options.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class OptionsPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */	
	public $tableName = 'option';

	public static $reservedTags = array('name', 'optiontype', 'defaultvalue', 'validationpattern', 'enableoptions', 'showorder', 'hidden', 'selectoptions', 'categoryname', 'permissions', 'options', 'attrs', 'cdata');
	
	/**
	 * @see	wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $defaultValue = $validationPattern = $selectOptions = $enableOptions = $permissions = $options = '';
		$showOrder = null;
		$hidden = 0;
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = WCF::getLanguage()->get($option['defaultvalue']);
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['showorder'])) $showOrder = intval($option['showorder']);
		if (isset($option['hidden'])) $hidden = intval($option['hidden']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['selectoptions'])) $selectOptions = $option['selectoptions'];
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		
		// check if optionType exists
		$className = 'wcf\system\option\OptionType'.ucfirst($optionType);
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// collect additional tags and their values
		$additionalData = array();
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// build update or create data
		$data = array(
			'categoryName' => $categoryName,
			'optionType' => $optionType,
			'validationPattern' => $validationPattern,
			'selectOptions' => $selectOptions,
			'showOrder' => $showOrder,
			'enableOptions' => $enableOptions,
			'hidden' => $hidden,
			'permissions' => $permissions,
			'options' => $options,
			'additionalData' => serialize($additionalData)
		);
		
		// try to find an existing option for updating
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	optionName = ?
				AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$optionName,
			$this->installation->getPackageID()
		));
		$row = $statement->fetchArray();
		
		// result was 'false' thus create a new item
		if (!$row) {
			$data['optionName'] = $optionName;
			$data['packageID'] = $this->installation->getPackageID();
			$data['optionValue'] = $defaultValue;
			
			OptionEditor::create($data);
		}
		else {
			// update existing item
			$optionObj = new Option(null, $row);
			$optionEditor = new OptionEditor($optionObj);
			$optionEditor->update($data);
		}
	}
}

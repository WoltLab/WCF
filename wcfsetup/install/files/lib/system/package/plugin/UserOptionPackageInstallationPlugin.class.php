<?php
namespace wcf\system\package\plugin;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_option';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	string[]
	 */
	public static $reservedTags = ['name', 'optiontype', 'defaultvalue', 'validationpattern', 'required', 'editable', 'visible', 'searchable', 'showorder', 'outputclass', 'selectoptions', 'enableoptions', 'isdisabled', 'categoryname', 'permissions', 'options', 'attrs', 'cdata'];
	
	/**
	 * @inheritDoc
	 */
	protected function saveCategory($category, $categoryXML = null) {
		// use for create and update
		$data = [
			'parentCategoryName' => $category['parentCategoryName'],
			'permissions' => $category['permissions'],
			'options' => $category['options']
		];
		// append show order if explicitly stated
		if ($category['showOrder'] !== null) $data['showOrder'] = $category['showOrder'];
		
		$userOptionCategory = UserOptionCategory::getCategoryByName($category['categoryName']);
		if ($userOptionCategory !== null) {
			if ($userOptionCategory->packageID != $this->installation->getPackageID()) {
				throw new SystemException("Cannot override existing category '".$category['categoryName']."'");
			}
			
			$categoryEditor = new UserOptionCategoryEditor($userOptionCategory);
			$categoryEditor->update($data);
		}
		else {
			// append data fields for create
			$data['packageID'] = $this->installation->getPackageID();
			$data['categoryName'] = $category['categoryName'];
			
			UserOptionCategoryEditor::create($data);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// default values
		$optionName = $optionType = $validationPattern = $outputClass = $selectOptions = $enableOptions = $permissions = $options = '';
		$required = $editable = $visible = $searchable = $isDisabled = $askDuringRegistration = 0;
		$defaultValue = $showOrder = null;
		
		// get values
		if (isset($option['name'])) $optionName = $option['name'];
		if (isset($option['optiontype'])) $optionType = $option['optiontype'];
		if (isset($option['defaultvalue'])) $defaultValue = $option['defaultvalue'];
		if (isset($option['validationpattern'])) $validationPattern = $option['validationpattern'];
		if (isset($option['required'])) $required = intval($option['required']);
		if (isset($option['askduringregistration'])) $askDuringRegistration = intval($option['askduringregistration']);
		if (isset($option['editable'])) $editable = intval($option['editable']);
		if (isset($option['visible'])) $visible = intval($option['visible']);
		if (isset($option['searchable'])) $searchable = intval($option['searchable']);
		if (isset($option['showorder'])) $showOrder = intval($option['showorder']);
		if (isset($option['outputclass'])) $outputClass = $option['outputclass'];
		if (isset($option['selectoptions'])) $selectOptions = $option['selectoptions'];
		if (isset($option['enableoptions'])) $enableOptions = $option['enableoptions'];
		if (isset($option['isdisabled'])) $isDisabled = intval($option['isdisabled']);
		$showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
		if (isset($option['permissions'])) $permissions = $option['permissions'];
		if (isset($option['options'])) $options = $option['options'];
		
		// collect additional tags and their values
		$additionalData = [];
		foreach ($option as $tag => $value) {
			if (!in_array($tag, self::$reservedTags)) $additionalData[$tag] = $value;
		}
		
		// get optionID if it was installed by this package already
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	optionName = ?
			AND	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$optionName,
			$this->installation->getPackageID()
		]);
		$result = $statement->fetchArray();
		
		// build data array
		$data = [
			'categoryName' => $categoryName,
			'optionType' => $optionType,
			'defaultValue' => $defaultValue,
			'validationPattern' => $validationPattern,
			'selectOptions' => $selectOptions,
			'enableOptions' => $enableOptions,
			'required' => $required,
			'askDuringRegistration' => $askDuringRegistration,
			'editable' => $editable,
			'visible' => $visible,
			'outputClass' => $outputClass,
			'searchable' => $searchable,
			'showOrder' => $showOrder,
			'isDisabled' => $isDisabled,
			'permissions' => $permissions,
			'options' => $options,
			'additionalData' => serialize($additionalData),
			'originIsSystem' => 1
		];
		
		// update option
		if (!empty($result['optionID']) && $this->installation->getAction() == 'update') {
			$userOption = new UserOption(null, $result);
			$userOptionEditor = new UserOptionEditor($userOption);
			$userOptionEditor->update($data);
		}
		// insert new option
		else {
			// append option name
			$data['optionName'] = $optionName;
			$data['packageID'] = $this->installation->getPackageID();
			UserOptionEditor::create($data);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// get optionsIDs from package
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_option
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		while ($row = $statement->fetchArray()) {
			WCF::getDB()->getEditor()->dropColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$row['optionID']);
		}
		
		// uninstall options and categories
		parent::uninstall();
	}
}

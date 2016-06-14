<?php
namespace wcf\system\option;
use wcf\data\category\CategoryNodeTree;
use wcf\data\option\Option;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Option type implementation for multi select lists.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
abstract class AbstractCategoryMultiSelectOptionType extends AbstractOptionType {
	/**
	 * object type name
	 * @var	string
	 */
	public $objectType = '';
	
	/**
	 * node tree class
	 * @var	string
	 */
	public $nodeTreeClassname = CategoryNodeTree::class;
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		/** @var CategoryNodeTree $categoryTree */
		$categoryTree = new $this->nodeTreeClassname($this->objectType);
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		
		WCF::getTPL()->assign([
			'categoryList' => $categoryList,
			'option' => $option,
			'value' => (!is_array($value) ? explode("\n", $value) : $value)
		]);
		return WCF::getTPL()->fetch('categoryMultiSelectOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		$newValue = ArrayUtil::toIntegerArray($newValue);
		
		foreach ($newValue as $categoryID) {
			$category = CategoryHandler::getInstance()->getCategory($categoryID);
			if ($category === null) throw new UserInputException($option->optionName, 'validationFailed');
			if ($category->getObjectType()->objectType != $this->objectType) throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		return implode("\n", ArrayUtil::toIntegerArray($newValue));
	}
}

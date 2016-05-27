<?php
namespace wcf\system\condition;
use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;
use wcf\system\category\CategoryHandler;

/**
 * Abstract implementation of a condition for selecting multiple categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractMultiCategoryCondition extends AbstractMultiSelectCondition {
	/**
	 * name of the category object type
	 * @var	string
	 */
	public $objectType = '';
	
	/**
	 * name of category node tree class
	 * @var	string
	 */
	public $nodeTreeClassname = CategoryNodeTree::class;
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		/** @noinspection PhpUndefinedMethodInspection */
		$categoryTree = (new $this->nodeTreeClassname($this->objectType))->getIterator();
		$categoryCount = iterator_count($categoryTree);
		
		$fieldElement = '<select name="'.$this->fieldName.'[]" id="'.$this->fieldName.'" multiple="multiple" size="'.($categoryCount >= 10 ? 10 : $categoryCount).'">';
		/** @var CategoryNode $categoryNode */
		foreach ($categoryTree as $categoryNode) {
			$fieldElement .= "<option value=\"{$categoryNode->categoryID}\"".(in_array($categoryNode->categoryID, $this->fieldValue) ? ' selected="selected"' : '').">".str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $categoryNode->getOpenParentNodes()).$categoryNode->getTitle()."</option>";
		}
		$fieldElement .= '</select>';
		
		return $fieldElement;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getOptions() {
		return CategoryHandler::getInstance()->getCategories($this->objectType);
	}
}

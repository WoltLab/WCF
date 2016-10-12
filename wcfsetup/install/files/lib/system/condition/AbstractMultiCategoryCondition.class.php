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
 * @package	WoltLabSuite\Core\System\Condition
 * @since	3.0
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
		
		$fieldElement = '<select name="'.$this->fieldName.'[]" id="'.$this->fieldName.'" multiple size="'.($categoryCount >= 10 ? 10 : $categoryCount).'">';
		/** @var CategoryNode $categoryNode */
		foreach ($categoryTree as $categoryNode) {
			$fieldElement .= "<option value=\"{$categoryNode->categoryID}\"".(in_array($categoryNode->categoryID, $this->fieldValue) ? ' selected' : '').">".str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $categoryNode->getOpenParentNodes()).$categoryNode->getTitle()."</option>";
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

<?php
namespace wcf\system\page\handler;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\ILinkableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ParentClassException;
use wcf\system\WCF;

/**
 * Provides the `isValid` and `lookup` methods for looking up decorated categories.
 * 
 * Note: This only works in the class extends `AbstractDecoratedCategory` and defines a
 * constant `OBJECT_TYPE_NAME` with the name of the `com.woltlab.wcf.category` object type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
trait TDecoratedCategoryLookupPageHandler {
	/**
	 * Returns the name of the decorated class name.
	 *
	 * @return	string
	 */
	abstract protected function getDecoratedCategoryClass();
	
	/**
	 * @see	ILookupPageHandler::getLink()
	 */
	public function getLink($objectID) {
		$className = $this->getDecoratedCategoryClass();
		$category = $className::getCategory($objectID);
		
		if ($category instanceof ILinkableObject) {
			return $category->getLink();
		}
		
		throw new \LogicException("If '" . $className . "' does not implement '" . ILinkableObject::class . "', the 'getLink' method needs to be overwritten.");
	}
	
	/**
	 * @see	ILookupPageHandler::isValid()
	 */
	public function isValid($objectID = null) {
		$className = $this->getDecoratedCategoryClass();
		
		return $className::getCategory($objectID)->isAccessible();
	}
	
	/**
	 * @see	ILookupPageHandler::lookup()
	 */
	public function lookup($searchString) {
		$className = $this->getDecoratedCategoryClass();
		if (!is_subclass_of($className, AbstractDecoratedCategory::class)) {
			throw new ParentClassException($className, AbstractDecoratedCategory::class);
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('category.objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', $className::OBJECT_TYPE_NAME)]);
		$conditionBuilder->add('(category.title LIKE ? OR language_item.languageItemValue LIKE ?)', ['%' . $searchString . '%', '%' . $searchString . '%']);
		$sql = "SELECT		DISTINCT categoryID
			FROM		wcf".WCF_N."_category category
			LEFT JOIN	wcf".WCF_N."_language_item language_item
			ON		(language_item.languageItem = category.title)
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql, 10);
		$statement->execute($conditionBuilder->getParameters());
		$results = [];
		while ($categoryID = $statement->fetchColumn()) {
			/** @var AbstractDecoratedCategory $category */
			$category = $className::getCategory($categoryID);
			
			// build hierarchy
			$description = '';
			foreach ($category->getParentCategories() as $parentCategory) {
				$description .= $parentCategory->getTitle() . ' &raquo; ';
			}
			
			$results[] = [
				'description' => $description,
				'image' => 'fa-folder-open-o',
				'link' => $category->getLink(),
				'objectID' => $categoryID,
				'title' => $category->getTitle()
			];
		}
		
		return $results;
	}
}
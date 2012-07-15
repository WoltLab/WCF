<?php
namespace wcf\data\category;
use wcf\system\exception\SystemException;
use wcf\system\category\CategoryHandler;

/**
 * Represents a category node list.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category 	Community Framework
 */
class CategoryNodeList extends \RecursiveIteratorIterator implements \Countable {
	/**
	 * number of (real) category nodes in this list
	 * @var	integer
	 */
	protected $count = null;
	
	/**
	 * name of the category node class
	 * @var	string
	 */
	protected $nodeClassName = 'wcf\data\category\CategoryNode';
	
	/**
	 * id of the parent category
	 * @var	integer
	 */
	protected $parentCategoryID = 0;
	
	/**
	 * Creates a new CategoryNodeList instance.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$parentCategoryID
	 * @param	boolean		$inludeDisabledCategories
	 * @param	array<integer>	$excludedCategoryIDs
	 */
	public function __construct($objectType, $parentCategoryID = 0, $inludeDisabledCategories = false, array $excludedObjectTypeCategoryIDs = array()) {
		$this->parentCategoryID = $parentCategoryID;
		
		$objectTypeID = CategoryHandler::getInstance()->getObjectTypeByName($objectType)->objectTypeID;
		
		// get parent category
		if (!$this->parentCategoryID) {
			// empty node
			$parentCategory = new Category(null, array(
				'categoryID' => $this->parentCategoryID,
				'objectTypeID' => $objectTypeID,
				'objectTypeCategoryID' => $this->parentCategoryID
			));
		}
		else {
			$parentCategory = CategoryHandler::getInstance()->getCategory($objectType, $this->parentCategoryID);
			if ($parentCategory === null) {
				throw new SystemException("There is no category with id '".$this->parentCategoryID."' and object type '".$objectType."'");
			}
		}
		
		parent::__construct(new $this->nodeClassName($parentCategory, $inludeDisabledCategories, $excludedObjectTypeCategoryIDs), \RecursiveIteratorIterator::SELF_FIRST);
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		if ($this->count === null) {
			$this->count = 0;
			foreach ($this as $categoryNode) {
				$this->count++;
			}
		}
		
		return $this->count;
	}
}

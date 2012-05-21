<?php
namespace wcf\data\category;
use wcf\data\object\type\ObjectTypeEditor;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\CacheHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Provides functions to edit categories.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category 	Community Framework
 */
class CategoryEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * @see wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
		// update show order
		if (isset($parameters['parentCategoryID']) || isset($parameters['showOrder'])) {
			if (!isset($parameters['parentCategoryID'])) {
				$parameters['parentCategoryID'] = $this->parentCategoryID;
			}
			
			if (!isset($parameters['showOrder'])) {
				$parameters['showOrder'] = $this->showOrder;
			}
			
			$parameters['showOrder'] = $this->updateShowOrder($parameters['parentCategoryID'], $parameters['showOrder']);
		}
		
		parent::update($parameters);
	}
	
	/**
	 * Prepares the update of the show order of this category and return the
	 * correct new show order.
	 * 
	 * @param	integer		$parentCategoryID
	 * @param	integer		$showOrder
	 * @return	integer
	 */
	protected function updateShowOrder($parentCategoryID, $showOrder) {
		// correct invalid values
		if ($showOrder <= 0) {
			$showOrder = 1;
		}
		
		if ($parentCategoryID != $this->parentCategoryID) {
			$sql = "UPDATE	".static::getDatabaseTableName()."
				SET 	showOrder = showOrder - 1
				WHERE 	showOrder > ?
					AND parentCategoryID = ?
					AND objectTypeID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->showOrder,
				$this->parentCategoryID,
				$this->objectTypeID
			));
			
			return static::getShowOrder($this->objectTypeID, $parentCategoryID, $showOrder);
		}
		else {
			if ($showOrder < $this->showOrder) {
				$sql = "UPDATE	".static::getDatabaseTableName()."
					SET 	showOrder = showOrder + 1
					WHERE 	showOrder >= ?
						AND showOrder < ?
						AND objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$showOrder,
					$this->showOrder,
					$this->objectTypeID
				));
			}
			else if ($showOrder > $this->showOrder) {
				$sql = "SELECT	MAX(showOrder) AS showOrder
					FROM	".static::getDatabaseTableName()."
					WHERE	objectTypeID = ?
						AND parentCategoryID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$this->objectTypeID,
					$this->parentCategoryID
				));
				$row = $statement->fetchArray();
				$maxShowOrder = 0;
				if (!empty($row)) {
					$maxShowOrder = intval($row['showOrder']);
				}
				
				if ($showOrder > $maxShowOrder) {
					$showOrder = $maxShowOrder;
				}
		
				$sql = "UPDATE	".static::getDatabaseTableName()."
					SET	showOrder = showOrder - 1
					WHERE	showOrder <= ?
						AND showOrder > ?
						AND objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$showOrder,
					$this->showOrder,
					$this->objectTypeID
				));
			}
			
			return $showOrder;
		}
	}
	
	/**
	 * @see wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		// handle time
		if (!isset($parameters['time'])) {
			$parameters['time'] = TIME_NOW;
		}
		
		// handle show order
		$parameters['showOrder'] = static::getShowOrder($parameters['objectTypeID'], $parameters['parentCategoryID'], $parameters['showOrder']);
		$parameters['objectTypeCategoryID'] = static::getNextCategoryID($parameters['objectTypeID']);
		
		// handle additionalData
		if (!isset($parameters['additionalData'])) {
			$parameters['additionalData'] = serialize(array());
		}
		
		return parent::create($parameters);
	}
	
	/**
	 * @see wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		// update positions
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	showOrder = showOrder - 1
			WHERE	parentCategoryID = ?
				AND showOrder > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($objectIDs as $categoryID) {
			$category = CategoryHandler::getInstance()->getCategoryByID($categoryID);
			$statement->execute(array($category->parentCategoryID, $category->showOrder));
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Returns the next category id for the given object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	integer
	 */
	protected static function getNextCategoryID($objectTypeID) {
		$objectType = CategoryHandler::getInstance()->getObjectType($objectTypeID);
		if ($objectType === null) {
			throw new SystemException("Invalid category object type id '".$objectTypeID."'");
		}
		
		$nextCategoryID = 1;
		if ($objectType->nextCategoryID !== null) {
			$nextCategoryID = $objectType->nextCategoryID;
		}
		
		// update next category additional data
		$objectTypeEditor = new ObjectTypeEditor($objectType);
		$objectTypeEditor->update(array(
			'additionalData' => serialize(array_merge($objectType->additionalData, array(
				'nextCategoryID' => $nextCategoryID + 1
			)))
		));
		
		// reset object type cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.objectType-*.php');
		
		return $nextCategoryID;
	}
	
	/**
	 * Returns the show order for a new category.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$parentCategoryID
	 * @param	integer		$showOrder
	 * @return	integer
	 */
	protected static function getShowOrder($objectTypeID, $parentCategoryID, $showOrder) {
		// correct invalid values
		if ($showOrder <= 0) {
			$showOrder = 1;
		}
		
		$sql = "SELECT	MAX(showOrder) AS showOrder
			FROM	".static::getDatabaseTableName()."
			WHERE	objectTypeID = ?
				AND parentCategoryID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeID,
			$parentCategoryID
		));
		$row = $statement->fetchArray();
		$maxShowOrder = 0;
		if (!empty($row)) {
			$maxShowOrder = intval($row['showOrder']);
		}
		
		if ($maxShowOrder && $showOrder <= $maxShowOrder) {
			$sql = "UPDATE	".static::getDatabaseTableName()."
				SET 	showOrder = showOrder + 1
				WHERE 	objectTypeID = ?
					AND showOrder >= ?
					AND parentCategoryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				$showOrder,
				$parentCategoryID
			));
			
			return $showOrder;
		}
		
		return $maxShowOrder + 1;
	}
	
	/**
	 * @see	wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.category-'.PACKAGE_ID.'.php');
	}
}

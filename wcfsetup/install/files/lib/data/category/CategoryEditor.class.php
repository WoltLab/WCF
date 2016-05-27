<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 *
 * @method	Category	getDecoratedObject()
 * @mixin	Category
 */
class CategoryEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Category::class;
	
	/**
	 * Prepares the update of the show order of this category and return the
	 * correct new show order.
	 * 
	 * @param	integer		$parentCategoryID
	 * @param	integer		$showOrder
	 * @return	integer
	 */
	public function updateShowOrder($parentCategoryID, $showOrder) {
		// correct invalid values
		if ($showOrder === null) {
			$showOrder = PHP_INT_MAX;
		}
		
		if ($parentCategoryID != $this->parentCategoryID) {
			$sql = "UPDATE	".static::getDatabaseTableName()."
				SET	showOrder = showOrder - 1
				WHERE	showOrder > ?
					AND parentCategoryID = ?
					AND objectTypeID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$this->showOrder,
				$this->parentCategoryID,
				$this->objectTypeID
			]);
			
			return static::getShowOrder($this->objectTypeID, $parentCategoryID, $showOrder);
		}
		else {
			if ($showOrder < $this->showOrder) {
				$sql = "UPDATE	".static::getDatabaseTableName()."
					SET	showOrder = showOrder + 1
					WHERE	showOrder >= ?
						AND showOrder < ?
						AND parentCategoryID = ?
						AND objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$showOrder,
					$this->showOrder,
					$this->parentCategoryID,
					$this->objectTypeID
				]);
			}
			else if ($showOrder > $this->showOrder) {
				$sql = "SELECT	MAX(showOrder) AS showOrder
					FROM	".static::getDatabaseTableName()."
					WHERE	objectTypeID = ?
						AND parentCategoryID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$this->objectTypeID,
					$this->parentCategoryID
				]);
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
				$statement->execute([
					$showOrder,
					$this->showOrder,
					$this->objectTypeID
				]);
			}
			
			return $showOrder;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		// default values
		$parameters['time'] = (isset($parameters['time'])) ? $parameters['time'] : TIME_NOW;
		$parameters['parentCategoryID'] = (isset($parameters['parentCategoryID'])) ? $parameters['parentCategoryID'] : 0;
		$parameters['showOrder'] = (isset($parameters['showOrder'])) ? $parameters['showOrder'] : null;
		
		// handle show order
		$parameters['showOrder'] = static::getShowOrder($parameters['objectTypeID'], $parameters['parentCategoryID'], $parameters['showOrder']);
		
		// handle additionalData
		if (!isset($parameters['additionalData'])) {
			$parameters['additionalData'] = serialize([]);
		}
		
		return parent::create($parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		// update positions
		$sql = "UPDATE	".static::getDatabaseTableName()."
			SET	showOrder = showOrder - 1
			WHERE	parentCategoryID = ?
				AND showOrder > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($objectIDs as $categoryID) {
			$category = CategoryHandler::getInstance()->getCategory($categoryID);
			$statement->execute([$category->parentCategoryID, $category->showOrder]);
		}
		
		return parent::deleteAll($objectIDs);
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
		if ($showOrder === null) {
			$showOrder = PHP_INT_MAX;
		}
		
		$sql = "SELECT	MAX(showOrder) AS showOrder
			FROM	".static::getDatabaseTableName()."
			WHERE	objectTypeID = ?
				AND parentCategoryID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$parentCategoryID
		]);
		$row = $statement->fetchArray();
		$maxShowOrder = 0;
		if (!empty($row)) {
			$maxShowOrder = intval($row['showOrder']);
		}
		
		if ($maxShowOrder && $showOrder <= $maxShowOrder) {
			$sql = "UPDATE	".static::getDatabaseTableName()."
				SET	showOrder = showOrder + 1
				WHERE	objectTypeID = ?
					AND showOrder >= ?
					AND parentCategoryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$objectTypeID,
				$showOrder,
				$parentCategoryID
			]);
			
			return $showOrder;
		}
		
		return $maxShowOrder + 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		CategoryCacheBuilder::getInstance()->reset();
	}
}

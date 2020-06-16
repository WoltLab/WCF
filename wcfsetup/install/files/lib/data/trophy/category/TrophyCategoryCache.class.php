<?php
namespace wcf\data\trophy\category;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Trophy category cache management.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy\Category
 * @since	3.1
 */
class TrophyCategoryCache extends SingletonFactory {
	/**
	 * All categories for trophies. 
	 * @var TrophyCategory[]
	 */
	protected $categories = [];
	
	/**
	 * All enabled categories for trophies.
	 * @var TrophyCategory[]
	 */
	protected $enabledCategories = [];
	
	/**
	 * @inheritDoc
	 */
	public function init() {
		$categoryData = CategoryCacheBuilder::getInstance()->getData();
		if (isset($categoryData['objectTypeCategoryIDs'][TrophyCategory::OBJECT_TYPE_NAME])) {
			$categoryIDs = $categoryData['objectTypeCategoryIDs'][TrophyCategory::OBJECT_TYPE_NAME];
			
			foreach ($categoryIDs as $categoryID) {
				$this->categories[$categoryID] = new TrophyCategory($categoryData['categories'][$categoryID]);
				
				if (!$categoryData['categories'][$categoryID]->isDisabled) {
					$this->enabledCategories[$categoryID] = $this->categories[$categoryID];
				}
			}
		}
	}
	
	/**
	 * Returns the trophy category with the given id.
	 *
	 * @param 	integer		$categoryID
	 * @return 	TrophyCategory|null
	 */
	public function getCategoryByID($categoryID) {
		if (isset($this->categories[$categoryID])) {
			return $this->categories[$categoryID];
		}
		
		return null;
	}
	
	/**
	 * Returns the categories with the given id.
	 *
	 * @param 	integer[]	$categoryIDs
	 * @return 	TrophyCategory[]
	 */
	public function getCategoriesByID(array $categoryIDs) {
		$returnValues = [];
		
		foreach ($categoryIDs as $categoryID) {
			$returnValues[] = $this->getCategoryByID($categoryID);
		}
		
		return $returnValues;
	}
	
	/**
	 * Return all categories.
	 *
	 * @return	TrophyCategory[]
	 */
	public function getCategories() {
		return $this->categories;
	}
	
	/**
	 * Return all enabled categories.
	 *
	 * @return	TrophyCategory[]
	 */
	public function getEnabledCategories() {
		return $this->enabledCategories;
	}
}

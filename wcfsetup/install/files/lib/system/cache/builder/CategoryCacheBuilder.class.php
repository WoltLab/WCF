<?php
namespace wcf\system\cache\builder;
use wcf\data\category\CategoryList;

/**
 * Caches the categories for a certain package.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CategoryCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$list = new CategoryList();
		$list->sqlLimit = 0;
		$list->sqlSelects = "object_type.objectType";
		$list->sqlJoins = "LEFT JOIN wcf".WCF_N."_object_type object_type ON (object_type.objectTypeID = category.objectTypeID)";
		$list->sqlOrderBy = "category.showOrder ASC";
		$list->readObjects();
		
		$data = array(
			'categories' => $list->getObjects(),
			'objectTypeCategoryIDs' => array()
		);
		foreach ($list as $category) {
			if (!isset($data['objectTypeCategoryIDs'][$category->objectType])) {
				$data['objectTypeCategoryIDs'][$category->objectType] = array();
			}
			
			$data['objectTypeCategoryIDs'][$category->objectType][] = $category->categoryID;
		}
		
		return $data;
	}
}

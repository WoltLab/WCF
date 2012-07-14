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
 * @category 	Community Framework
 */
class CategoryCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list(, $packageID) = explode('-', $cacheResource['cache']);
		
		$list = new CategoryList();
		$list->sqlLimit = 0;
		$list->sqlJoins = "	LEFT JOIN	wcf".WCF_N."_object_type object_type
					ON		(object_type.objectTypeID = category.objectTypeID)
					LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
					ON		(package_dependency.dependency = object_type.packageID)";
		$list->getConditionBuilder()->add("package_dependency.packageID = ?", array($packageID));
		$list->sqlOrderBy = "package_dependency.priority ASC, category.showOrder ASC";
		$list->readObjects();
		
		$data = array(
			'categories' => array(),
			'categoryIDs' => array()
		);
		foreach ($list as $category) {
			if (!isset($data['categories'][$category->objectTypeID])) {
				$data['categories'][$category->objectTypeID] = array();
			}
			
			$data['categories'][$category->objectTypeID][$category->objectTypeCategoryID] = $category;
			$data['categoryIDs'][$category->categoryID] = array(
				'objectTypeID' => $category->objectTypeID,
				'objectTypeCategoryID' => $category->objectTypeCategoryID
			);
		}
		
		return $data;
	}
}

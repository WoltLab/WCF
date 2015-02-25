<?php
namespace wcf\system\cache\builder;
use wcf\data\category\CategoryList;

/**
 * Caches the categories for the active application.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CategoryCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$list = new CategoryList();
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

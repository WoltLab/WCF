<?php
namespace wcf\system\cache\builder;
use wcf\data\category\CategoryList;

/**
 * Caches the categories for the active application.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class CategoryCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$list = new CategoryList();
		$list->sqlSelects = "object_type.objectType";
		$list->sqlJoins = "LEFT JOIN wcf".WCF_N."_object_type object_type ON (object_type.objectTypeID = category.objectTypeID)";
		$list->sqlOrderBy = "category.showOrder ASC";
		$list->readObjects();
		
		$data = [
			'categories' => $list->getObjects(),
			'objectTypeCategoryIDs' => []
		];
		foreach ($list as $category) {
			if (!isset($data['objectTypeCategoryIDs'][$category->objectType])) {
				$data['objectTypeCategoryIDs'][$category->objectType] = [];
			}
			
			$data['objectTypeCategoryIDs'][$category->objectType][] = $category->categoryID;
		}
		
		return $data;
	}
}

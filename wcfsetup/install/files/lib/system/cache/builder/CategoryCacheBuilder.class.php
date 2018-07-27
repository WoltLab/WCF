<?php
namespace wcf\system\cache\builder;
use wcf\data\category\CategoryList;

/**
 * Caches the categories for the active application.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
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
			/** @noinspection PhpUndefinedFieldInspection */
			$objectType = $category->objectType;
			
			if (!isset($data['objectTypeCategoryIDs'][$objectType])) {
				$data['objectTypeCategoryIDs'][$objectType] = [];
			}
			
			$data['objectTypeCategoryIDs'][$objectType][] = $category->categoryID;
		}
		
		return $data;
	}
}

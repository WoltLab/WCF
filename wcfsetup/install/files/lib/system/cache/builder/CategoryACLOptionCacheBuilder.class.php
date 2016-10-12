<?php
namespace wcf\system\cache\builder;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;

/**
 * Caches the acl options of categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class CategoryACLOptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [];
		foreach (CategoryHandler::getInstance()->getCategories() as $objectTypeName => $categories) {
			$objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
			$aclObjectType = $objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
			if (!$aclObjectType) {
				continue;
			}
			
			$aclOptions = ACLHandler::getInstance()->getPermissions(ACLHandler::getInstance()->getObjectTypeID($aclObjectType), array_keys($categories));
			/** @noinspection PhpUndefinedMethodInspection */
			$options = $aclOptions['options']->getObjects();
			
			foreach (['group', 'user'] as $type) {
				foreach ($aclOptions[$type] as $categoryID => $optionData) {
					if (!isset($data[$categoryID])) {
						$data[$categoryID] = [
							'group' => [],
							'user' => []
						];
					}
					
					foreach ($optionData as $typeID => $optionValues) {
						$data[$categoryID][$type][$typeID] = [];
						
						foreach ($optionValues as $optionID => $optionValue) {
							$data[$categoryID][$type][$typeID][$options[$optionID]->optionName] = $optionValue;
						}
					}
				}
			}
		}
		
		return $data;
	}
}

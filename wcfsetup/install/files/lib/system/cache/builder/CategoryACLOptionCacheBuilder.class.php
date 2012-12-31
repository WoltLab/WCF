<?php
namespace wcf\system\cache\builder;
use wcf\system\category\CategoryHandler;
use wcf\system\acl\ACLHandler;

/**
 * Caches the acl options of categories.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CategoryACLOptionCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array();
		foreach (CategoryHandler::getInstance()->getCategories() as $objectTypeName => $categories) {
			$objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
			$aclObjectType = $objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
			if (!$aclObjectType) {
				continue;
			}
			
			$aclOptions = ACLHandler::getInstance()->getPermissions(ACLHandler::getInstance()->getObjectTypeID($aclObjectType), array_keys($categories));
			$options = $aclOptions['options']->getObjects();
			
			$data = array();
			foreach (array('group', 'user') as $type) {
				foreach ($aclOptions[$type] as $categoryID => $optionData) {
					if (!isset($aclValues[$categoryID])) {
						$data[$categoryID] = array(
							'group' => array(),
							'user' => array()
						);
					}

					foreach ($optionData as $typeID => $optionValues) {
						$data[$categoryID][$type][$typeID] = array();
						
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

<?php
namespace wcf\system\cache\builder;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;

/**
 * Caches the acl options of categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CategoryACLOptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		foreach (CategoryHandler::getInstance()->getCategories() as $objectTypeName => $categories) {
			$objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
			$aclObjectType = $objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
			if (!$aclObjectType) {
				continue;
			}
			
			$aclOptions = ACLHandler::getInstance()->getPermissions(ACLHandler::getInstance()->getObjectTypeID($aclObjectType), array_keys($categories));
			$options = $aclOptions['options']->getObjects();
			
			foreach (array('group', 'user') as $type) {
				foreach ($aclOptions[$type] as $categoryID => $optionData) {
					if (!isset($data[$categoryID])) {
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

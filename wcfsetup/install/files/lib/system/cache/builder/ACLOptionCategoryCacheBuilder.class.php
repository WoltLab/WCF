<?php
namespace wcf\system\cache\builder;
use wcf\data\acl\option\category\ACLOptionCategoryList;

/**
 * Caches the acl categories for a certain package.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACLOptionCategoryCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$list = new ACLOptionCategoryList();
		$list->sqlLimit = 0;
		$list->readObjects();
		
		$data = array();
		foreach ($list as $aclOptionCategory) {
			if (!isset($data[$aclOptionCategory->objectTypeID])) {
				$data[$aclOptionCategory->objectTypeID] = array();
			}
			
			$data[$aclOptionCategory->objectTypeID][$aclOptionCategory->categoryName] = $aclOptionCategory;
		}
		
		return $data;
	}
}

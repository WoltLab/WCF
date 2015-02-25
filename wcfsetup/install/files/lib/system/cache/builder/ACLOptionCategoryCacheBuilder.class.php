<?php
namespace wcf\system\cache\builder;
use wcf\data\acl\option\category\ACLOptionCategoryList;

/**
 * Caches the acl categories for a certain package.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACLOptionCategoryCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$list = new ACLOptionCategoryList();
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

<?php
namespace wcf\system\cache;
use wcf\data\template\group\TemplateGroupList;

/**
 * Caches template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderTemplateGroup implements CacheBuilder {
	/**
	 * @see wcf\system\cache\CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->sqlLimit = 0;
		$templateGroupList->readObjects();
		
		return $templateGroupList->getObjects();
	}
}

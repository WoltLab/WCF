<?php
namespace wcf\system\cache\builder;
use wcf\data\template\group\TemplateGroupList;
use wcf\system\cache\CacheBuilder;

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
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->sqlLimit = 0;
		$templateGroupList->readObjects();
		
		return $templateGroupList->getObjects();
	}
}

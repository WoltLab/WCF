<?php
namespace wcf\system\cache\builder;
use wcf\data\template\group\TemplateGroupList;

/**
 * Caches template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TemplateGroupCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->readObjects();
		
		return $templateGroupList->getObjects();
	}
}

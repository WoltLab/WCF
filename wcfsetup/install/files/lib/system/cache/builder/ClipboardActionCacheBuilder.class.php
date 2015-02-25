<?php
namespace wcf\system\cache\builder;
use wcf\data\clipboard\action\ClipboardActionList;

/**
 * Caches clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ClipboardActionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$actionList = new ClipboardActionList();
		$actionList->readObjects();
		
		return $actionList->getObjects();
	}
}

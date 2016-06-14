<?php
namespace wcf\system\cache\builder;
use wcf\data\clipboard\action\ClipboardActionList;

/**
 * Caches clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class ClipboardActionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$actionList = new ClipboardActionList();
		$actionList->readObjects();
		
		return $actionList->getObjects();
	}
}

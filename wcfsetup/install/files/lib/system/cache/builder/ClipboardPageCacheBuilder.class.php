<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches clipboard pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class ClipboardPageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$sql = "SELECT	pageClassName, actionID
			FROM	wcf".WCF_N."_clipboard_page";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		return $statement->fetchMap('pageClassName', 'actionID', false);
	}
}

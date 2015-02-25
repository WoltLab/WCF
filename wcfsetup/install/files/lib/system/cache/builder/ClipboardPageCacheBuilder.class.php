<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches clipboard pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ClipboardPageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$sql = "SELECT	pageClassName, actionID
			FROM	wcf".WCF_N."_clipboard_page";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$data = array();
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['pageClassName']])) {
				$data[$row['pageClassName']] = array();
			}
			
			$data[$row['pageClassName']][] = $row['actionID'];
		}
		
		return $data;
	}
}

<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches clipboard pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ClipboardPageCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
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

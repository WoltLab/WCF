<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches cronjob information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CronjobCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$sql = "SELECT		MIN(nextExec) AS nextExec,
					MIN(afterNextExec) AS afterNextExec
			FROM		wcf".WCF_N."_cronjob";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		
		return array(
			'afterNextExec' => $row['afterNextExec'],
			'nextExec' => $row['nextExec']
		);
	}
}

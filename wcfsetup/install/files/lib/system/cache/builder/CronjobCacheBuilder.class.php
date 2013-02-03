<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches cronjob information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CronjobCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
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

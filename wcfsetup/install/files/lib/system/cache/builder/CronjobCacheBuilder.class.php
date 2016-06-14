<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches cronjob information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class CronjobCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$sql = "SELECT	MIN(nextExec) AS nextExec,
				MIN(afterNextExec) AS afterNextExec
			FROM	wcf".WCF_N."_cronjob
			WHERE	isDisabled = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0]);
		$row = $statement->fetchArray();
		
		return [
			'afterNextExec' => $row['afterNextExec'],
			'nextExec' => $row['nextExec']
		];
	}
}

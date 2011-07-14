<?php
namespace wcf\system\cache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Caches cronjob information.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderCronjob implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		// get next execution time
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("packageID IN (?)", array(PackageDependencyHandler::getDependenciesString()));
		
		$sql = "SELECT		MIN(nextExec) AS nextExec,
					MIN(afterNextExec) AS afterNextExec
			FROM		wcf".WCF_N."_cronjob
			".$conditionBuilder->__toString();
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$row = $statement->fetchArray();
		
		return array(
			'afterNextExec' => $row['afterNextExec'],
			'nextExec' => $row['nextExec']
		);
	}
}
?>

<?php
namespace wcf\system\stat;
use wcf\system\WCF;

/**
 * Abstract stat handler implementation for disk usage.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
abstract class AbstractDiskUsageStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @see	\wcf\system\stat\AbstractStatDailyHandler::getCounter()
	 */
	protected function getCounter($date, $tableName, $dateColumnName) {
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	".$tableName."
			WHERE	".$dateColumnName." BETWEEN ? AND ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date, $date + 86399));
		return $statement->fetchColumn();
	}
	
	/**
	 * @see	\wcf\system\stat\AbstractStatDailyHandler::getTotal()
	 */
	protected function getTotal($date, $tableName, $dateColumnName) {
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	".$tableName."
			WHERE	".$dateColumnName." < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date + 86400));
		return $statement->fetchColumn();
	}
	
	/**
	 * @see	\wcf\system\stat\IStatDailyHandler::getFormattedCounter()
	 */
	public function getFormattedCounter($counter) {
		return round($counter / 1000, 2); // return mb
	}
}

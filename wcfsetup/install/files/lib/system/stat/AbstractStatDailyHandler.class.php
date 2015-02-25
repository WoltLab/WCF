<?php
namespace wcf\system\stat;
use wcf\system\WCF;

/**
 * Abstract implementation of a stat handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
abstract class AbstractStatDailyHandler implements IStatDailyHandler {
	/**
	 * Counts the number of rows for a single day.
	 * 
	 * @param	integer		$date
	 * @param	string		$tableName
	 * @param	string		$dateColumnName
	 * @return	integer
	 */
	protected function getCounter($date, $tableName, $dateColumnName) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	" . $tableName . "
			WHERE	" . $dateColumnName . " BETWEEN ? AND ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date, $date + 86399));
		return $statement->fetchColumn();
	}
	
	/**
	 * Counts the total number of rows.
	 * 
	 * @param	integer		$date
	 * @param	string		$tableName
	 * @param	string		$dateColumnName
	 * @return	integer
	 */
	protected function getTotal($date, $tableName, $dateColumnName) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	" . $tableName . "
			WHERE	" . $dateColumnName . " < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date + 86400));
		return $statement->fetchColumn();
	}
	
	/**
	 * @see	\wcf\system\stat\IStatDailyHandler::getFormattedCounter()
	 */
	public function getFormattedCounter($counter) {
		return $counter;
	}
}

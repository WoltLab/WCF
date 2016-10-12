<?php
namespace wcf\system\stat;
use wcf\system\WCF;

/**
 * Abstract stat handler implementation for disk usage.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Stat
 */
abstract class AbstractDiskUsageStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	protected function getCounter($date, $tableName, $dateColumnName) {
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	".$tableName."
			WHERE	".$dateColumnName." BETWEEN ? AND ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$date, $date + 86399]);
		return $statement->fetchColumn();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTotal($date, $tableName, $dateColumnName) {
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	".$tableName."
			WHERE	".$dateColumnName." < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$date + 86400]);
		return $statement->fetchColumn();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedCounter($counter) {
		return round($counter / 1000, 2); // return mb
	}
}

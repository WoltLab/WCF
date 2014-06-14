<?php
namespace wcf\system\stat;
use wcf\system\WCF;

/**
 * Stat handler implementation for attachment disk usage.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
class AttachmentDiskUsageStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @see	\wcf\system\stat\IStatDailyHandler::getData()
	 */
	public function getData($date) {
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	wcf".WCF_N."_attachment
			WHERE	uploadTime BETWEEN ? AND ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date, $date + 86399));
		$counter = intval($statement->fetchColumn());
		
		$sql = "SELECT	CEIL(SUM(filesize) / 1000)
			FROM	wcf".WCF_N."_attachment
			WHERE	uploadTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date + 86400));
		$total = intval($statement->fetchColumn());
		
		return array(
			'counter' => $counter,
			'total' => $total
		);
	}
	
	/**
	 * @see	\wcf\system\stat\IStatDailyHandler::getFormattedCounter()
	 */
	public function getFormattedCounter($counter) {
		return round($counter / 1000, 2); // return mb
	}
}

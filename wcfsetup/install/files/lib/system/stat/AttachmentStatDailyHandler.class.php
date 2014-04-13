<?php
namespace wcf\system\stat;
use wcf\system\WCF;

/**
 * Stat handler implementation for attachment stats.
 *
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
class AttachmentStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @see \wcf\system\stat\IStatDailyHandler::getData()
	 */
	public function getData($date) {
		$sql = "SELECT	SUM(filesize)
			FROM	wcf".WCF_N."_attachment
			WHERE	uploadTime BETWEEN ? AND ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($date, $date + 86400));
		$counter = intval($statement->fetchColumn());
		
		$sql = "SELECT	SUM(filesize)
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
}

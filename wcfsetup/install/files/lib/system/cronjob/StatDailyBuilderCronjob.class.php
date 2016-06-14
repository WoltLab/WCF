<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Builds daily statistics.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class StatDailyBuilderCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// get date
		$d = DateUtil::getDateTimeByTimestamp(TIME_NOW);
		$d->setTimezone(new \DateTimeZone(TIMEZONE));
		$d->sub(new \DateInterval('P1D'));
		$d->setTime(0, 0);
		$date = $d->getTimestamp();
		
		// prepare insert statement
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_stat_daily
						(objectTypeID, date, counter, total)
			VALUES			(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// get object types
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler') as $objectType) {
			$data = $objectType->getProcessor()->getData($date);
			
			$statement->execute([$objectType->objectTypeID, $d->format('Y-m-d'), $data['counter'], $data['total']]);
		}
	}
}

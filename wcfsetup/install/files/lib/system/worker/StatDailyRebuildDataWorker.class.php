<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Worker implementation for updating daily statistics.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class StatDailyRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $limit = 30;
	
	/**
	 * start timestamp
	 * @var	integer
	 */
	protected $startDate = 0;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {}
	
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		$this->getStartDate();
		
		$this->count = ceil((TIME_NOW - $this->startDate) / 86400);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		EventHandler::getInstance()->fireAction($this, 'execute');
		
		if (!$this->loopCount) {
			// delete existing stat
			$sql = "DELETE FROM	wcf".WCF_N."_stat_daily";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		// prepare insert statement
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_stat_daily
						(objectTypeID, date, counter, total)
			VALUES			(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$this->getStartDate();
		$d = DateUtil::getDateTimeByTimestamp($this->startDate);
		$d->setTimezone(new \DateTimeZone(TIMEZONE));
		$d->setTime(0, 0);
		if ($this->loopCount) {
			$d->add(new \DateInterval('P'.($this->loopCount * $this->limit).'D'));
		}
		for ($i = 0; $i < $this->limit; $i++) {
			if ($d->getTimestamp() > TIME_NOW) break;
			
			// get object types
			foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler') as $objectType) {
				$data = $objectType->getProcessor()->getData($d->getTimestamp());
				$statement->execute([$objectType->objectTypeID, $d->format('Y-m-d'), $data['counter'], $data['total']]);
			}
			
			$d->add(new \DateInterval('P1D'));
		}
	}
	
	/**
	 * Gets the start timestamp.
	 * 
	 * @return	integer
	 */
	protected function getStartDate() {
		if ($this->startDate) return;
		$sql = "SELECT	MIN(registrationDate)
			FROM	wcf".WCF_N."_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$this->startDate = $statement->fetchColumn();
	}
}

<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows daily statistics.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class StatPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.stat.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * start date (yyyy-mm-dd)
	 * @var	string
	 */
	public $startDate = '';
	
	/**
	 * end date (yyyy-mm-dd)
	 * @var	string
	 */
	public $endDate = '';
	
	/**
	 * available object type
	 * @var	array
	 */
	public $availableObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// set default values
		$d = DateUtil::getDateTimeByTimestamp(TIME_NOW - 86400);
		$d->setTimezone(WCF::getUser()->getTimeZone());
		$this->endDate = $d->format('Y-m-d');
		$d->sub(new \DateInterval('P1M'));
		$this->startDate = $d->format('Y-m-d');
		
		// get object types
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler');
		foreach ($objectTypes as $objectType) {
			if (!isset($this->availableObjectTypes[$objectType->categoryname])) {
				$this->availableObjectTypes[$objectType->categoryname] = [];
			}
			
			$this->availableObjectTypes[$objectType->categoryname][] = $objectType;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'endDate' => $this->endDate,
			'startDate' => $this->startDate,
			'availableObjectTypes' => $this->availableObjectTypes
		]);
	}
}

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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class StatPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.stat';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * start date (yyyy-mm-dd)
	 * @var string
	 */
	public $startDate = '';
	
	/**
	 * end date (yyyy-mm-dd)
	 * @var string
	 */
	public $endDate = '';
	
	/**
	 * available object type
	 * @var array
	 */
	public $availableObjectTypes = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// set default values
		$d = DateUtil::getDateTimeByTimestamp(1366416000); // @todo TIME_NOW
		$d->setTimezone(WCF::getUser()->getTimeZone());
		$this->endDate = $d->format('Y-m-d');
		$d->sub(new \DateInterval('P1M'));
		$this->startDate = $d->format('Y-m-d');
		
		// get object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler');
	}
	
	/**
	 * @see	\wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		WCF::getTPL()->assign(array(
			'endDate' => $this->endDate,
			'startDate' => $this->startDate,
			'availableObjectTypes' => $this->availableObjectTypes
		));
	}
}

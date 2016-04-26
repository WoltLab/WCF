<?php
namespace wcf\page;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\page\PageLocationManager;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Shows the global recent activity list page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class RecentActivityListPage extends AbstractPage {
	/**
	 * viewable user activity event list
	 * @var	ViewableUserActivityEventList
	 */
	public $eventList = null;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->eventList = new ViewableUserActivityEventList();
		$this->eventList->readObjects();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$lastEventTime = $this->eventList->getLastEventTime();
		
		// removes orphaned and non-accessable events
		UserActivityEventHandler::validateEvents($this->eventList);
		
		WCF::getTPL()->assign([
			'eventList' => $this->eventList,
			'lastEventTime' => $lastEventTime,
			'allowSpidersToIndexThisPage' => true
		]);
	}
}

<?php
namespace wcf\page;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\WCF;

/**
 * Shows the global recent activity list page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class RecentActivityListPage extends AbstractPage {
	/**
	 * viewable user activity event list
	 * @var	\wcf\data\user\activity\event\ViewableUserActivityEventList
	 */
	public $eventList = null;
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->eventList = new ViewableUserActivityEventList();
		$this->eventList->readObjects();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('wcf.user.members'), LinkHandler::getInstance()->getLink('MembersList')));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$lastEventTime = $this->eventList->getLastEventTime();
		
		// removes orphaned and non-accessable events
		UserActivityEventHandler::validateEvents($this->eventList);
		
		WCF::getTPL()->assign(array(
			'eventList' => $this->eventList,
			'lastEventTime' => $lastEventTime,
			'allowSpidersToIndexThisPage' => true
		));
	}
}

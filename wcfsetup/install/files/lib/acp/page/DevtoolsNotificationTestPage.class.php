<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\user\notification\event\ITestableUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list of available testable user notification events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.1
 */
class DevtoolsNotificationTestPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.notificationTest';
	
	/**
	 * available testable user notification events
	 * @var	ITestableUserNotificationEvent[][]
	 */
	protected $events = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->events = UserNotificationHandler::getInstance()->getAvailableEvents();
		
		// filter events
		foreach ($this->events as $objectTypeID => $events) {
			foreach ($events as $eventName => $event) {
				if (!$event instanceof ITestableUserNotificationEvent) {
					unset($this->events[$objectTypeID][$eventName]);
				}
			}
			
			if (empty($this->events[$objectTypeID])) {
				unset($this->events[$objectTypeID]);
			}
		}
		
		$groupedEvents = [];
		foreach ($this->events as $objectType => $events) {
			$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.notification.objectType', $objectType);
			$category = ($objectTypeObj->category ?: $objectType);
			
			if (!isset($groupedEvents[$category])) {
				$groupedEvents[$category] = [];
			}
			
			foreach ($events as $event) $groupedEvents[$category][] = $event;
		}
		
		ksort($groupedEvents);
		
		$this->events = $groupedEvents;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'events' => $this->events
		]);
	}
}

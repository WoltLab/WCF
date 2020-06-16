<?php
namespace wcf\system\user\notification\event;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableUserNotificationEvent {
	/**
	 * description of the specfic test case
	 * @var	string
	 */
	protected $testCaseDescription = '';
	
	/**
	 * @see	ITestableUserNotificationEvent::canBeTriggeredByGuests()
	 */
	public static function canBeTriggeredByGuests() {
		return false;
	}
	
	/**
	 * @see	ITestableUserNotificationEvent::getTestCaseDescription()
	 */
	public function getTestCaseDescription() {
		return $this->testCaseDescription;
	}
	
	/**
	 * @see	ITestableUserNotificationEvent::setTestCaseDescription()
	 */
	public function setTestCaseDescription($description) {
		$this->testCaseDescription = $description;
	}
	
	/**
	 * @see	ITestableUserNotificationEvent::getTestAdditionalData()
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		return [];
	}
}

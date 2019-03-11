<?php
namespace wcf\system\user\notification\event;
use wcf\data\language\Language;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Every testable user notification event has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
interface ITestableUserNotificationEvent extends IUserNotificationEvent {
	/**
	 * Returns the language of the event.
	 *
	 * @return	Language
	 */
	public function getLanguage();
	
	/**
	 * Returns the description of the covered test case.
	 *
	 * @return	string
	 */
	public function getTestCaseDescription();
	
	/**
	 * Sets the description of the covered test case.
	 *
	 * @param	string		$description
	 */
	public function setTestCaseDescription($description);
	
	/**
	 * @return	boolean
	 */
	public static function canBeTriggeredByGuests();
	
	/**
	 * Returns additional data for given user notification object.
	 * The test data has to be the same data given when an actual event is fired.
	 * 
	 * @param	IUserNotificationObject		$object
	 * @return	array
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object);
	
	/**
	 * Returns a test user notification object for the given recipient and
	 * caused by the given author.
	 * 
	 * @param	UserProfile	$recipient
	 * @param	UserProfile	$author
	 * @return	IUserNotificationObject[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author);
}

<?php
namespace wcf\system\user\notification\object;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\IIDObject;
use wcf\data\ITitledObject;

/**
 * This interface should be implemented by every object which is part of a notification.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2019 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object
 */
interface IUserNotificationObject extends IDatabaseObjectProcessor, IIDObject, ITitledObject {
	/**
	 * Returns the url of this object.
	 * 
	 * @return	string
	 */
	public function getURL();
	
	/**
	 * Returns the user id of the author of this object.
	 * 
	 * @return	int
	 */
	public function getAuthorID();
}

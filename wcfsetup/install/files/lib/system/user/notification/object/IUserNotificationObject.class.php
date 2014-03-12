<?php
namespace wcf\system\user\notification\object;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\ITitledObject;

/**
 * This interface should be implemented by every object which is part of a notification.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2014 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object
 * @category	Community Framework
 */
interface IUserNotificationObject extends IDatabaseObjectProcessor, ITitledObject {
	/**
	 * Returns the ID of this object.
	 * 
	 * @return	integer
	 */
	public function getObjectID();
	
	/**
	 * Returns the url of this object.
	 * 
	 * @return	string
	 */
	public function getURL();
	
	/**
	 * Returns the user id of the author of this object.
	 * 
	 * @return	integer
	 */
	public function getAuthorID();
}

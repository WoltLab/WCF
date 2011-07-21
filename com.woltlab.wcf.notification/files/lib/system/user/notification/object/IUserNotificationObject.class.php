<?php
namespace wcf\system\user\notification\object;
use wcf\data\IDatabaseObjectProcessor;

/**
 * This interface should be implemented by every object which is part of a notification.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.object
 * @category 	Community Framework
 */
interface IUserNotificationObject extends IDatabaseObjectProcessor {
	/**
	 * Returns the ID of this object.
	 *
	 * @return	integer
	 */
	public function getObjectID();

	/**
	 * Returns the title of this object.
	 *
	 * @return	string
	 */
	public function getTitle();

	/**
	 * Returns the url of this object.
	 *
	 * @return	string
	 */
	public function getURL();
}

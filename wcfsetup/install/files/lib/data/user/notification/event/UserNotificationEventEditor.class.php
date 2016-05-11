<?php
namespace wcf\data\user\notification\event;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification.event
 * @category	Community Framework
 * 
 * @method	UserNotificationEvent	getDecoratedObject()
 * @mixin	UserNotificationEvent
 */
class UserNotificationEventEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserNotificationEvent::class;
}

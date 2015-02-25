<?php
namespace wcf\system\user\notification\object\type;

/**
 * User notification object type implementation for likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class LikeUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$decoratorClassName
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\LikeUserNotificationObject';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectClassName
	 */
	protected static $objectClassName = 'wcf\data\like\Like';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectListClassName
	 */
	protected static $objectListClassName = 'wcf\data\like\LikeList';
}

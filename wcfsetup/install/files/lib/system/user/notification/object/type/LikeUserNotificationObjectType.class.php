<?php
namespace wcf\system\user\notification\object\type;

/**
 * User notification object type implementation for likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class LikeUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\LikeUserNotificationObject';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = 'wcf\data\like\Like';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = 'wcf\data\like\LikeList';
}

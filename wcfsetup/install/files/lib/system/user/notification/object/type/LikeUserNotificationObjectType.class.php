<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\like\Like;
use wcf\data\like\LikeList;
use wcf\system\user\notification\object\LikeUserNotificationObject;

/**
 * User notification object type implementation for likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
class LikeUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = LikeUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Like::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = LikeList::class;
}

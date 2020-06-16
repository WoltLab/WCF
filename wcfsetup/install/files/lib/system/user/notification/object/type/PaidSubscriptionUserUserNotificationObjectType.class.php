<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\system\user\notification\object\PaidSubscriptionUserUserNotificationObject;

/**
 * Represents a paid subscription user notification object type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	3.1
 */
class PaidSubscriptionUserUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = PaidSubscriptionUserUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = PaidSubscriptionUser::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = PaidSubscriptionUserList::class;
}

<?php
namespace wcf\system\user\notification\object;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a following user as a notification object.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object
 *
 * @method	UserTrophy	getDecoratedObject()
 * @mixin	UserTrophy
 */
class UserTrophyNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritdoc
	 */
	protected static $baseClass = UserTrophy::class;
	
	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTrophy()->getTitle();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getURL() {
		return $this->getDecoratedObject()->getTrophy()->getLink();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getAuthorID() {
		return $this->getDecoratedObject()->userID;
	}
	
}

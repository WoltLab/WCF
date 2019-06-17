<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * This trait can be used in likeable user notification events to determine the reactionType counts
 * for a specific user notification object.
 * 
 * HEADS UP: This trait is only included in version 3.0 and 3.1 for compatibility reasons. The method 
 * getReactionsForAuthors() throws a \BadMethodCallException exception, if it used in version 3.0 or 3.1.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.0
 */
trait TReactionUserNotificationEvent {
	/**
	 * Cached reactions
	 * @var int[]
	 */
	private $cachedReactions;
	
	/**
	 * Returns the count of reactionTypeIDs for the specific user notification object.
	 *
	 * @return      int[]
	 * @throws      \BadMethodCallException
	 */
	protected final function getReactionsForAuthors() {
		throw new \BadMethodCallException("The method is only supported from version 5.2 and only available for compatibility reasons.");
	}
	
	/**
	 * Returns the author for this notification event.
	 *
	 * @return	UserProfile
	 */
	abstract public function getAuthor();
	
	/**
	 * Returns a list of authors for stacked notifications sorted by time.
	 *
	 * @return	UserProfile[]
	 */
	abstract public function getAuthors();
	
	/**
	 * Returns the underlying user notification object.
	 *
	 * @return	IUserNotificationObject
	 */
	abstract public function getUserNotificationObject();
}

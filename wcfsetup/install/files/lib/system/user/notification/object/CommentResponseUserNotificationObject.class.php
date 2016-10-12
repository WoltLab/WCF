<?php
namespace wcf\system\user\notification\object;
use wcf\data\comment\response\CommentResponse;
use wcf\data\DatabaseObjectDecorator;

/**
 * Notification object for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object
 * 
 * @method	CommentResponse		getDecoratedObject()
 * @mixin	CommentResponse
 */
class CommentResponseUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->userID;
	}
}

<?php
namespace wcf\system\user\notification\object;
use wcf\data\article\Article;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents an article user notification object.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object
 *
 * @method	Article	        getDecoratedObject()
 * @mixin	Article
 */
class ArticleUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Article::class;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->getDecoratedObject()->userID;
	}
}

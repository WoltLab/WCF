<?php
namespace wcf\data\article;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\Like;

/**
 * Likeable object implementation for cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 * 
 * @method	Article	getDecoratedObject()
 * @mixin	Article
 */
class LikeableArticle extends AbstractLikeObject {
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
	public function getUserID() {
		return $this->userID;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->articleID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateLikeCounter($cumulativeLikes) {
		// update cumulative likes
		$editor = new ArticleEditor($this->getDecoratedObject());
		$editor->update(['cumulativeLikes' => $cumulativeLikes]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendNotification(Like $like) {
		/* @todo
		if ($this->getDecoratedObject()->userID != WCF::getUser()->userID) {
			$notificationObject = new LikeUserNotificationObject($like);
			UserNotificationHandler::getInstance()->fireEvent('like', 'com.woltlab.wcf.likeableArticle.notification', $notificationObject, [$this->getDecoratedObject()->userID], [
				'objectID' => $this->getDecoratedObject()->articleID
			]);
		}
		*/
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getLanguageID() {
		return null;
	}
}

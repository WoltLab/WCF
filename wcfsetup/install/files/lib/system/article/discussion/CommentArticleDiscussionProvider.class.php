<?php
namespace wcf\system\article\discussion;
use wcf\data\article\Article;
use wcf\system\comment\CommentHandler;
use wcf\system\WCF;

/**
 * The built-in discussion provider using the native comment system.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Article\Discussion
 * @since       5.2
 */
class CommentArticleDiscussionProvider extends AbstractArticleDiscussionProvider {
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCount() {
		return $this->article->comments;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDiscussionCountPhrase() {
		return WCF::getLanguage()->getDynamicVariable('wcf.article.articleComments', ['article' => $this->article]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function renderDiscussions() {
		$commentCanAdd = WCF::getSession()->getPermission('user.article.canAddComment');
		$commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.articleComment');
		$commentManager = CommentHandler::getInstance()->getObjectType($commentObjectTypeID)->getProcessor();
		$commentList = CommentHandler::getInstance()->getCommentList($commentManager, $commentObjectTypeID, $this->articleContent->articleContentID);
		
		WCF::getTPL()->assign([
			'commentCanAdd' => $commentCanAdd,
			'commentList' => $commentList,
			'commentObjectTypeID' => $commentObjectTypeID,
			'lastCommentTime' => $commentList->getMinCommentTime(),
			'likeData' => (MODULE_LIKE) ? $commentList->getLikeData() : [],
		]);
		
		return WCF::getTPL()->fetch('articleComments');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isResponsible(Article $article) {
		return !!$article->enableComments;
	}
}

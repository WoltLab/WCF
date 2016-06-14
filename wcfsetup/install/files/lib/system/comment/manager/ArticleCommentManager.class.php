<?php
namespace wcf\system\comment\manager;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\ArticleEditor;
use wcf\system\WCF;

/**
 * Article comment manager implementation.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
 */
class ArticleCommentManager extends AbstractCommentManager {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.article.canAddComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.article.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.article.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.article.canDeleteComment';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.article.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$articleContent = new ArticleContent($objectID);
		if (!$articleContent->articleContentID || !$articleContent->getArticle()->canRead()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return (new ArticleContent($objectID))->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.article.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.article.comment');
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {
		$articleContent = new ArticleContent($objectID);
		$editor = new ArticleEditor($articleContent->getArticle());
		$editor->updateCounters([
			'comments' => $value
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsLike() {
		// @todo
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsReport() {
		// @todo
		return false;
	}
}

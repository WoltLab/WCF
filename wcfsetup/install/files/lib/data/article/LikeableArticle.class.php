<?php
namespace wcf\data\article;
use wcf\data\like\object\AbstractLikeObject;

/**
 * Likeable object implementation for cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getLanguageID() {
		return null;
	}
}

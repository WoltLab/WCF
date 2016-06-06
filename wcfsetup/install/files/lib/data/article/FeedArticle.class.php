<?php
namespace wcf\data\article;
use wcf\data\IFeedEntry;
use wcf\data\TUserContent;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Represents a viewable article for RSS feeds.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
 */
class FeedArticle extends ViewableArticle implements IFeedEntry {
	use TUserContent;
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return $this->getExcerpt();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getTeaser();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getExcerpt($maxLength = 255) {
		return StringUtil::encodeHTML($this->getDecoratedObject()->getTeaser());
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getMessage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getComments() {
		return $this->comments;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCategories() {
		$categories = [];
		$category = $this->getDecoratedObject()->getCategory();
		if ($category !== null) {
			$categories[] = $category->getTitle();
			foreach ($category->getParentCategories() as $category) {
				$categories[] = $category->getTitle();
			}
		}
		
		return $categories;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		return $this->canRead();
	}
}

<?php
namespace wcf\data\article;
use wcf\data\IFeedEntryWithEnclosure;
use wcf\data\TUserContent;
use wcf\system\feed\enclosure\FeedEnclosure;
use wcf\util\StringUtil;

/**
 * Represents a viewable article for RSS feeds.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 */
class FeedArticle extends ViewableArticle implements IFeedEntryWithEnclosure {
	use TUserContent;
	
	/**
	 * @var FeedEnclosure
	 */
	protected $enclosure;
	
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
	
	/**
	 * @inheritDoc
	 */
	public function getEnclosure() {
		if ($this->enclosure === null) {
			if ($this->getImage() !== null) {
				$this->enclosure = new FeedEnclosure($this->getImage()->getThumbnailLink('small'), $this->getImage()->smallThumbnailType, $this->getImage()->smallThumbnailSize);
			}
		}
		
		return $this->enclosure;
	}
}

<?php
namespace wcf\data\article\content;
use wcf\data\article\ViewableArticle;
use wcf\data\media\ViewableMedia;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable article content.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 *
 * @method	ArticleContent	getDecoratedObject()
 * @mixin	ArticleContent
 */
class ViewableArticleContent extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ArticleContent::class;
	
	/**
	 * article image
	 * @var ViewableMedia
	 */
	protected $image;
	
	/**
	 * article object
	 * @var ViewableArticle
	 */
	protected $article;
	
	/**
	 * Gets a specific article content decorated as viewable article content.
	 *
	 * @param	integer		$articleContentID
	 * @return	ViewableArticleContent
	 */
	public static function getArticleContent($articleContentID) {
		$list = new ViewableArticleContentList();
		$list->setObjectIDs([$articleContentID]);
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$articleContentID])) return $objects[$articleContentID];
		return null;
	}
	
	/**
	 * Returns article object.
	 *
	 * @return ViewableArticle
	 */
	public function getArticle() {
		if ($this->article === null) {
			$this->article = new ViewableArticle($this->getDecoratedObject()->getArticle());
		}
		
		return $this->article;
	}
	
	/**
	 * Sets the article objects.
	 *
	 * @param ViewableArticle $article
	 */
	public function setArticle(ViewableArticle $article) {
		$this->article = $article;
	}
	
	/**
	 * Returns the article's image.
	 *
	 * @return ViewableMedia
	 */
	public function getImage() {
		if ($this->image === null) {
			if ($this->imageID) {
				$this->image = ViewableMedia::getMedia($this->imageID);
				
				$this->image->setLinkParameters(['articleID' => $this->articleID]);
			}
		}
		
		return $this->image;
	}
	
	/**
	 * Sets the article's image.
	 * 
	 * @param	ViewableMedia	$image
	 */
	public function setImage(ViewableMedia $image) {
		$this->image = $image;
		$this->image->setLinkParameters(['articleID' => $this->articleID]);
	}
}

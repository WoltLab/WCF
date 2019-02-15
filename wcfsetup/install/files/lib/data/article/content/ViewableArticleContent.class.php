<?php
namespace wcf\data\article\content;
use wcf\data\article\ViewableArticle;
use wcf\data\media\ViewableMedia;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable article content.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
	 * article thumbnail image
	 * @var ViewableMedia
	 */
	protected $teaserImage;
	
	/**
	 * article object
	 * @var ViewableArticle
	 */
	protected $article;
	
	/**
	 * Returns article object.
	 * 
	 * @return	ViewableArticle
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
	 * @param	ViewableArticle		$article
	 */
	public function setArticle(ViewableArticle $article) {
		$this->article = $article;
	}
	
	/**
	 * Returns the article's image if the active user can access it or `null`.
	 * 
	 * @return	ViewableMedia|null
	 */
	public function getImage() {
		if ($this->image === null) {
			if ($this->imageID) {
				$this->image = ViewableMedia::getMedia($this->imageID);
			}
		}
		
		if ($this->image === null || !$this->image->isAccessible()) {
			return null;
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
	}
	
	/**
	 * Returns the article's teaser image if the active user can access it or `null`.
	 *
	 * @return	ViewableMedia|null
	 */
	public function getTeaserImage() {
		if (!$this->teaserImageID) {
			return $this->getImage();
		}
		
		if ($this->teaserImage === null) {
			$this->teaserImage = ViewableMedia::getMedia($this->teaserImageID);
		}
		
		if ($this->teaserImage === null || !$this->teaserImage->isAccessible()) {
			return null;
		}
		
		return $this->teaserImage;
	}
	
	/**
	 * Sets the article's teaser image.
	 *
	 * @param	ViewableMedia	$image
	 */
	public function setTeaserImage(ViewableMedia $image) {
		$this->teaserImage = $image;
	}
	
	/**
	 * Returns a specific article content decorated as viewable article content.
	 * 
	 * @param	integer		$articleContentID
	 * @return	ViewableArticleContent
	 */
	public static function getArticleContent($articleContentID) {
		$list = new ViewableArticleContentList();
		$list->setObjectIDs([$articleContentID]);
		$list->readObjects();
		
		return $list->search($articleContentID);
	}
}

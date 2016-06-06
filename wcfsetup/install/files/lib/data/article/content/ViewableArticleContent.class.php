<?php
namespace wcf\data\article\content;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\media\ViewableMedia;

/**
 * Represents a viewable article content.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
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
	 * Returns the article's image.
	 *
	 * @return ViewableMedia
	 */
	public function getImage() {
		if ($this->image === null) {
			if ($this->imageID) {
				$this->image = ViewableMedia::getMedia($this->imageID);
			}
		}
		
		return $this->image;
	}
	
	/**
	 * Sets the article's image.
	 * 
	 * @param ViewableMedia $image
	 */
	public function setImage(ViewableMedia $image) {
		$this->image = $image;
	}
}

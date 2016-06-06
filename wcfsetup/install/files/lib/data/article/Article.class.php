<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\media\ViewableMedia;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\WCF;

/**
 * Represents a cms article.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
 *
 * @property-read	integer		$articleID
 * @property-read	integer		$userID
 * @property-read	string		$username
 * @property-read	integer		$time
 * @property-read	integer		$categoryID
 * @property-read	integer		$isMultilingual
 * @property-read       integer         $publicationStatus
 * @property-read       integer         $publicationDate
 * @property-read       integer         $enableComments
 * @property-read       integer         $comments
 * @property-read       integer         $views
 * @todo
 */
class Article extends DatabaseObject implements ILinkableObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'article';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'articleID';
	
	/**
	 * article content grouped by language id
	 * @var	ArticleContent[]
	 */
	public $articleContent;
	
	/**
	 * article's category
	 * @var ArticleCategory
	 */
	protected $category;
	
	/**
	 * Returns true if the active user can delete this article.
	 *
	 * @return	boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if the active user has access to this article.
	 *
	 * @return	boolean
	 */
	public function canRead() {
		if ($this->getCategory()) {
			return $this->getCategory()->isAccessible();
		}
		
		return WCF::getSession()->getPermission('user.article.canRead');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$this->getArticleContent();
		if ($this->isMultilingual) {
			if (isset($this->articleContent[WCF::getLanguage()->languageID])) {
				return $this->articleContent[WCF::getLanguage()->languageID]->getLink();
			}
		}
		else {
			if (isset($this->articleContent[0])) {
				return $this->articleContent[0]->getLink();
			}
		}
		
		return '';
	}
	
	/**
	 * Returns the article's title.
	 *
	 * @return string
	 */
	public function getTitle() {
		$this->getArticleContent();
		if ($this->isMultilingual) {
			if (isset($this->articleContent[WCF::getLanguage()->languageID])) {
				return $this->articleContent[WCF::getLanguage()->languageID]->getTitle();
			}
		}
		else {
			if (isset($this->articleContent[0])) {
				return $this->articleContent[0]->getTitle();
			}
		}
		
		return '';
	}
	
	/**
	 * Returns the article's teaser.
	 *
	 * @return string
	 */
	public function getTeaser() {
		$this->getArticleContent();
		if ($this->isMultilingual) {
			if (isset($this->articleContent[WCF::getLanguage()->languageID])) {
				return $this->articleContent[WCF::getLanguage()->languageID]->teaser;
			}
		}
		else {
			if (isset($this->articleContent[0])) {
				return $this->articleContent[0]->teaser;
			}
		}
		
		return '';
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return string
	 */
	public function getFormattedContent() {
		$this->getArticleContent();
		if ($this->isMultilingual) {
			if (isset($this->articleContent[WCF::getLanguage()->languageID])) {
				return $this->articleContent[WCF::getLanguage()->languageID]->getFormattedContent();
			}
		}
		else {
			if (isset($this->articleContent[0])) {
				return $this->articleContent[0]->getFormattedContent();
			}
		}
		
		return '';
	}
	
	/**
	 * Returns the article's image.
	 *
	 * @return ViewableMedia
	 */
	public function getImage() {
		$this->getArticleContent();
		if ($this->isMultilingual) {
			if (isset($this->articleContent[WCF::getLanguage()->languageID])) {
				return $this->articleContent[WCF::getLanguage()->languageID]->getImage();
			}
		}
		else {
			if (!empty($this->articleContent[0])) {
				return $this->articleContent[0]->getImage();
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the article's content.
	 *
	 * @return	ArticleContent[]
	 */
	public function getArticleContent() {
		if ($this->articleContent === null) {
			$this->articleContent = [];
			
			$sql = "SELECT	*
				FROM	wcf" . WCF_N . "_article_content
				WHERE	articleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->articleID]);
			while ($row = $statement->fetchArray()) {
				$this->articleContent[($row['languageID'] ?: 0)] = new ArticleContent(null, $row);
			}
		}
		
		return $this->articleContent;
	}
	
	/**
	 * Returns the category of the article.
	 *
	 * @return	ArticleCategory
	 */
	public function getCategory() {
		if ($this->category === null && $this->categoryID) {
			$this->category = ArticleCategory::getCategory($this->categoryID);
		}
		
		return $this->category;
	}
}

<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\article\discussion\CommentArticleDiscussionProvider;
use wcf\system\article\discussion\IArticleDiscussionProvider;
use wcf\system\article\discussion\VoidArticleDiscussionProvider;
use wcf\system\WCF;

/**
 * Represents a cms article.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 *
 * @property-read	integer		$articleID		unique id of the article
 * @property-read	integer|null	$userID			id of the user the article belongs to or `null` if the user does not exist anymore
 * @property-read	string		$username		name of the user the article belongs to
 * @property-read	integer		$time			timestamp at which the comment has been written
 * @property-read	integer		$categoryID		id of the category the article belongs to
 * @property-read	integer		$isMultilingual		is `1` if the article is available in multiple languages, otherwise `0`
 * @property-read	integer		$publicationStatus	publication status of the article (see `Article::UNPUBLISHED`, `Article::PUBLISHED` and `Article::DELAYED_PUBLICATION`)
 * @property-read	integer		$publicationDate	timestamp at which the article will be automatically published or `0` if it has already been published
 * @property-read	integer		$enableComments		is `1` if comments are enabled for the article, otherwise `0`
 * @property-read	integer		$comments		number of comments on the article
 * @property-read	integer		$views			number of times the article has been viewed
 * @property-read	integer		$cumulativeLikes	cumulative result of likes (counting `+1`) and dislikes (counting `-1`) for the article
 * @property-read	integer		$isDeleted		is 1 if the article is in trash bin, otherwise 0
 * @property-read	integer		$hasLabels		is `1` if labels are assigned to the article
 */
class Article extends DatabaseObject implements ILinkableObject {
	/**
	 * indicates that article is unpublished
	 */
	const UNPUBLISHED = 0;
	
	/**
	 * indicates that article is published
	 */
	const PUBLISHED = 1;
	
	/**
	 * indicates that the publication of an article is delayed
	 */
	const DELAYED_PUBLICATION = 2;
	
	/**
	 * article content grouped by language id
	 * @var	ArticleContent[]
	 */
	public $articleContents;
	
	/**
	 * language links
	 * @var	ArticleContent[]
	 */
	public $languageLinks;
	
	/**
	 * article's category
	 * @var ArticleCategory
	 */
	protected $category;
	
	/**
	 * @var IArticleDiscussionProvider
	 * @since 3.2
	 */
	protected $discussionProvider;
	
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
		if ($this->isDeleted && !WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
			return false;
		}
		
		if ($this->publicationStatus != self::PUBLISHED) {
			if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle') && (!WCF::getSession()->getPermission('admin.content.article.canContributeArticle') || $this->userID != WCF::getUser()->userID)) {
				return false;
			}
		}
		
		if ($this->getCategory()) {
			return $this->getCategory()->isAccessible();
		}
		
		return WCF::getSession()->getPermission('user.article.canRead');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if ($this->getArticleContent() !== null) {
			return $this->getArticleContent()->getLink();
		}
		
		return '';
	}
	
	/**
	 * Returns the article's title.
	 *
	 * @return      string
	 */
	public function getTitle() {
		if ($this->getArticleContent() !== null) {
			return $this->getArticleContent()->getTitle();
		}
		
		return '';
	}
	
	/**
	 * Returns the article's unformatted teaser.
	 *
	 * @return      string
	 */
	public function getTeaser() {
		if ($this->getArticleContent() !== null) {
			return $this->getArticleContent()->getTeaser();
		}
		
		return '';
	}
	
	/**
	 * Returns the article's formatted teaser.
	 *
	 * @return      string
	 */
	public function getFormattedTeaser() {
		if ($this->getArticleContent() !== null) {
			return $this->getArticleContent()->getFormattedTeaser();
		}
		
		return '';
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedContent() {
		if ($this->getArticleContent() !== null) {
			return $this->getArticleContent()->getFormattedContent();
		}
		
		return '';
	}
	
	/**
	 * Returns the active content version.
	 *
	 * @return	ArticleContent|null
	 */
	public function getArticleContent() {
		$this->getArticleContents();
		
		if ($this->isMultilingual) {
			if (isset($this->articleContents[WCF::getLanguage()->languageID])) {
				return $this->articleContents[WCF::getLanguage()->languageID];
			}
		}
		else {
			if (!empty($this->articleContents[0])) {
				return $this->articleContents[0];
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the article's content.
	 *
	 * @return	ArticleContent[]
	 */
	public function getArticleContents() {
		if ($this->articleContents === null) {
			$this->articleContents = [];
			
			$sql = "SELECT	*
				FROM	wcf" . WCF_N . "_article_content
				WHERE	articleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->articleID]);
			while ($row = $statement->fetchArray()) {
				$this->articleContents[$row['languageID'] ?: 0] = new ArticleContent(null, $row);
			}
		}
		
		return $this->articleContents;
	}
	
	/**
	 * Returns the article's language links.
	 *
	 * @return	ArticleContent[]
	 */
	public function getLanguageLinks() {
		if ($this->languageLinks === null) {
			$this->languageLinks = [];
			$sql = "SELECT	articleContentID, title, languageID
				FROM	wcf" . WCF_N . "_article_content
				WHERE	articleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->articleID]);
			while ($row = $statement->fetchArray()) {
				$this->languageLinks[$row['languageID'] ?: 0] = new ArticleContent(null, $row);
			}
		}
		
		return $this->languageLinks;
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
	
	/**
	 * Sets the discussion provider for this article.
	 * 
	 * @param       IArticleDiscussionProvider      $discussionProvider
	 * @since       3.2
	 */
	public function setDiscussionProvider(IArticleDiscussionProvider $discussionProvider) {
		$this->discussionProvider = $discussionProvider;
	}
	
	/**
	 * Returns the responsible discussion provider for this article.
	 * 
	 * @return      IArticleDiscussionProvider
	 * @since       3.2
	 */
	public function getDiscussionProvider() {
		if ($this->discussionProvider === null) {
			foreach (self::getAllDiscussionProviders() as $discussionProvider) {
				if (call_user_func([$discussionProvider, 'isResponsible'], $this)) {
					$this->setDiscussionProvider(new $discussionProvider($this));
					break;
				}
			}
			
			if ($this->discussionProvider === null) {
				throw new \RuntimeException('No discussion provider has claimed to be responsible for the article #' . $this->articleID);
			}
		}
		
		return $this->discussionProvider;
	}
	
	/**
	 * Returns the list of the available discussion providers.
	 * 
	 * @return      string[]
	 * @since       3.2
	 */
	public static function getAllDiscussionProviders() {
		/** @var string[] $discussionProviders */
		static $discussionProviders;
		
		if ($discussionProviders === null) {
			$discussionProviders = [];
			
			$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.article.discussionProvider');
			$commentProvider = '';
			foreach ($objectTypes as $objectType) {
				// the comment and the "void" provider should always be the last in the list
				if ($objectType->className === CommentArticleDiscussionProvider::class) {
					$commentProvider = $objectType->className;
					continue;
				}
				
				$discussionProviders[] = $objectType->className;
			}
			
			$discussionProviders[] = $commentProvider;
			$discussionProviders[] = VoidArticleDiscussionProvider::class;
		}
		
		return $discussionProviders;
	}
}

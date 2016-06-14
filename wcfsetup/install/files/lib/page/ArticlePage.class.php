<?php
namespace wcf\page;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\AccessibleArticleList;
use wcf\data\article\ArticleEditor;
use wcf\data\article\CategoryArticleList;
use wcf\data\article\ViewableArticle;
use wcf\data\comment\StructuredCommentList;
use wcf\data\like\object\LikeObject;
use wcf\data\tag\Tag;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\like\LikeHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\MetaTagHandler;
use wcf\system\WCF;

/**
 * Shows a cms article.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class ArticlePage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
	
	/**
	 * article content id
	 * @var	integer
	 */
	public $articleContentID = 0;
	
	/**
	 * article content object
	 * @var	ViewableArticleContent
	 */
	public $articleContent;
	
	/**
	 * article object
	 * @var	ViewableArticle
	 */
	public $article;
	
	/**
	 * next article in this category
	 * @var	ViewableArticle
	 */
	public $nextArticle;
	
	/**
	 * previous article in this category
	 * @var	ViewableArticle
	 */
	public $previousArticle;
	
	/**
	 * comment object type id
	 * @var	integer
	 */
	public $commentObjectTypeID = 0;
	
	/**
	 * comment manager object
	 * @var	ICommentManager
	 */
	public $commentManager;
	
	/**
	 * list of comments
	 * @var	StructuredCommentList
	 */
	public $commentList;
	
	/**
	 * list of related articles
	 * @var AccessibleArticleList
	 */
	public $relatedArticles;
	
	/**
	 * list of tags
	 * @var	Tag[]
	 */
	public $tags = [];
	
	/**
	 * like data for the article
	 * @var	LikeObject[]
	 */
	public $articleLikeData = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->articleContentID = intval($_REQUEST['id']);
		$this->articleContent = ViewableArticleContent::getArticleContent($this->articleContentID);
		if ($this->articleContent === null) {
			throw new IllegalLinkException();
		}
		$this->article = ViewableArticle::getArticle($this->articleContent->articleID);
		$this->canonicalURL = $this->articleContent->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->article->canRead()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// update view count
		$articleEditor = new ArticleEditor($this->article->getDecoratedObject());
		$articleEditor->updateCounters([
			'views' => 1
		]);
		
		// get comments
		if ($this->article->enableComments) {
			$this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.articleComment');
			$this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
			$this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->articleContent->articleContentID);
		}
		
		// get next entry
		$articleList = new CategoryArticleList($this->article->categoryID);
		$articleList->getConditionBuilder()->add('article.time > ?', [$this->article->time]);
		$articleList->sqlOrderBy = 'article.time';
		$articleList->sqlLimit = 1;
		$articleList->readObjects();
		foreach ($articleList as $article) $this->nextArticle = $article;
		
		// get previous entry
		$articleList = new CategoryArticleList($this->article->categoryID);
		$articleList->getConditionBuilder()->add('article.time < ?', [$this->article->time]);
		$articleList->sqlOrderBy = 'article.time';
		$articleList->sqlLimit = 1;
		$articleList->readObjects();
		foreach ($articleList as $article) $this->previousArticle = $article;
		
		// get tags
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
			$this->tags = TagEngine::getInstance()->getObjectTags(
				'com.woltlab.wcf.article',
				$this->articleContent->articleContentID,
				[($this->articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID())]
			);
		}
		
		// get related articles
		if (MODULE_TAGGING && ARTICLE_RELATED_ARTICLES) {
			if (!empty($this->tags)) {
				$conditionBuilder = new PreparedStatementConditionBuilder();
				$conditionBuilder->add('objectTypeID = ?', [TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article')]);
				$conditionBuilder->add('tagID IN (?)', [array_keys($this->tags)]);
				$conditionBuilder->add('objectID <> ?', [$this->articleContentID]);
				$sql = "SELECT		objectID, COUNT(*) AS count
					FROM		wcf" . WCF_N . "_tag_to_object
					" . $conditionBuilder . "
					GROUP BY	objectID
					HAVING		COUNT(*) > " . (round(count($this->tags) * (ARTICLE_RELATED_ARTICLES_MATCH_THRESHOLD / 100))) . "
					ORDER BY	count DESC";
				$statement = WCF::getDB()->prepareStatement($sql, ARTICLE_RELATED_ARTICLES);
				$statement->execute($conditionBuilder->getParameters());
				$articleContentIDs = [];
				while ($row = $statement->fetchArray()) {
					$articleContentIDs[] = $row['objectID'];
				}
				
				if (!empty($articleContentIDs)) {
					$conditionBuilder = new PreparedStatementConditionBuilder();
					$conditionBuilder->add('articleContentID IN (?)', [$articleContentIDs]);
					$sql = "SELECT		articleID
						FROM		wcf" . WCF_N . "_article_content
						" . $conditionBuilder;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditionBuilder->getParameters());
					$articleIDs = [];
					while ($row = $statement->fetchArray()) {
						$articleIDs[] = $row['articleID'];
					}
					
					$this->relatedArticles = new AccessibleArticleList();
					$this->relatedArticles->getConditionBuilder()->add('article.articleID IN (?)', [$articleIDs]);
					$this->relatedArticles->sqlOrderBy = 'article.time';
					$this->relatedArticles->readObjects();
				}
			}
		}
		
		// fetch likes
		if (MODULE_LIKE) {
			$objectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.likeableArticle');
			LikeHandler::getInstance()->loadLikeObjects($objectType, [$this->article->articleID]);
			$this->articleLikeData = LikeHandler::getInstance()->getLikeObjects($objectType);
		}
		
		// set location
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $this->article->categoryID, $this->article->getCategory());
		foreach ($this->article->getCategory()->getParentCategories() as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $parentCategory->categoryID, $parentCategory);
		}
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.ArticleList');
		
		// add meta/og tags
		MetaTagHandler::getInstance()->addTag('og:title', 'og:title', $this->articleContent->getTitle() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE), true);
		MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('Article', ['object' => $this->articleContent, 'appendSession' => false]), true);
		MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
		MetaTagHandler::getInstance()->addTag('og:description', 'og:description', $this->articleContent->teaser, true);
		
		if ($this->articleContent->getImage()) {
			MetaTagHandler::getInstance()->addTag('og:image', 'og:image', $this->articleContent->getImage()->getLink(), true);
			MetaTagHandler::getInstance()->addTag('og:image:width', 'og:image:width', $this->articleContent->getImage()->width, true);
			MetaTagHandler::getInstance()->addTag('og:image:height', 'og:image:height', $this->articleContent->getImage()->height, true);
		}
		
		// add tags as keywords
		if (!empty($this->tags)) {
			$keywords = '';
			foreach ($this->tags as $tag) {
				if (!empty($keywords)) $keywords .= ', ';
				$keywords .= $tag->name;
			}
			MetaTagHandler::getInstance()->addTag('keywords', 'keywords', $keywords);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'articleContentID' => $this->articleContentID,
			'articleContent' => $this->articleContent,
			'article' => $this->article,
			'category' => $this->article->getCategory(),
			'previousArticle' => $this->previousArticle,
			'nextArticle' => $this->nextArticle,
			'commentCanAdd' => WCF::getSession()->getPermission('user.article.canAddComment'),
			'commentList' => $this->commentList,
			'commentObjectTypeID' => $this->commentObjectTypeID,
			'lastCommentTime' => ($this->commentList ? $this->commentList->getMinCommentTime() : 0),
			'likeData' => ((MODULE_LIKE && $this->commentList) ? $this->commentList->getLikeData() : []),
			'relatedArticles' => $this->relatedArticles,
			'tags' => $this->tags,
			'articleLikeData' => $this->articleLikeData
		]);
	}
}

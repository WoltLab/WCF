<?php
namespace wcf\page;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\AccessibleArticleList;
use wcf\data\article\ArticleAction;
use wcf\data\article\ArticleEditor;
use wcf\data\article\ViewableArticle;
use wcf\data\tag\Tag;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\page\PageLocationManager;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Abstract implementation of the article page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
abstract class AbstractArticlePage extends AbstractPage {
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
	 * list of tags
	 * @var	Tag[]
	 */
	public $tags = [];
	
	/**
	 * category object
	 * @var ArticleCategory
	 */
	public $category;
	
	/**
	 * list of related articles
	 * @var AccessibleArticleList
	 */
	public $relatedArticles;
	
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
		
		// check if the language has been disabled
		if ($this->articleContent->languageID && LanguageFactory::getInstance()->getLanguage($this->articleContent->languageID) === null) {
			throw new IllegalLinkException();
		}
		
		$this->article = ViewableArticleRuntimeCache::getInstance()->getObject($this->articleContent->articleID);
		$this->article->getDiscussionProvider()->setArticleContent($this->articleContent->getDecoratedObject());
		$this->category = $this->article->getCategory();
		
		// update interface language
		if (!WCF::getUser()->userID && $this->article->isMultilingual && $this->articleContent->languageID != WCF::getLanguage()->languageID) {
			WCF::setLanguage($this->articleContent->languageID);
		}
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
		
		// update article visit
		if (ARTICLE_ENABLE_VISIT_TRACKING && $this->article->isNew()) {
			$articleAction = new ArticleAction([$this->article->getDecoratedObject()], 'markAsRead', [
				'viewableArticle' => $this->article
			]);
			$articleAction->executeAction();
		}
		
		// get tags
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
			$this->tags = TagEngine::getInstance()->getObjectTags(
				'com.woltlab.wcf.article',
				$this->articleContent->articleContentID,
				[$this->articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
			);
		}
		
		// get related articles
		if (MODULE_TAGGING && ARTICLE_RELATED_ARTICLES) {
			if (!empty($this->tags)) {
				$conditionBuilder = new PreparedStatementConditionBuilder();
				$conditionBuilder->add('tag_to_object.objectTypeID = ?', [TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article')]);
				$conditionBuilder->add('tag_to_object.tagID IN (?)', [array_keys($this->tags)]);
				$conditionBuilder->add('tag_to_object.objectID <> ?', [$this->articleContentID]);
				$sql = "SELECT		article.articleID, COUNT(*) AS count
					FROM		wcf" . WCF_N . "_tag_to_object tag_to_object
					INNER JOIN	wcf" . WCF_N . "_article_content article_content
					ON		tag_to_object.objectID = article_content.articleContentID
					INNER JOIN	wcf" . WCF_N . "_article article
					ON		article_content.articleID = article.articleID
					" . $conditionBuilder . "
					GROUP BY	tag_to_object.objectID
					HAVING		COUNT(*) >= " . round(count($this->tags) * ARTICLE_RELATED_ARTICLES_MATCH_THRESHOLD / 100) . "
					ORDER BY	count DESC, article.time DESC";
				$statement = WCF::getDB()->prepareStatement($sql, ARTICLE_RELATED_ARTICLES);
				$statement->execute($conditionBuilder->getParameters());
				$articleIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
				
				if (!empty($articleIDs)) {
					$this->relatedArticles = new AccessibleArticleList();
					$this->relatedArticles->getConditionBuilder()->add('article.articleID IN (?)', [$articleIDs]);
					$this->relatedArticles->sqlOrderBy = 'article.time';
					$this->relatedArticles->readObjects();
				}
			}
		}
		
		// set location
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $this->article->categoryID, $this->article->getCategory());
		foreach (array_reverse($this->article->getCategory()->getParentCategories()) as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $parentCategory->categoryID, $parentCategory);
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
			'category' => $this->category,
			'relatedArticles' => $this->relatedArticles,
			'tags' => $this->tags
		]);
	}
}

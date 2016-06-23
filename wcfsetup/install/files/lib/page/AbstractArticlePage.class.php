<?php
namespace wcf\page;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\AccessibleArticleList;
use wcf\data\article\ArticleEditor;
use wcf\data\article\ViewableArticle;
use wcf\data\tag\Tag;
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
 * @copyright	2001-2016 WoltLab GmbH
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
		$this->article = ViewableArticle::getArticle($this->articleContent->articleID, false);
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
		
		// set location
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $this->article->categoryID, $this->article->getCategory());
		foreach ($this->article->getCategory()->getParentCategories() as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $parentCategory->categoryID, $parentCategory);
		}
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.ArticleList');
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

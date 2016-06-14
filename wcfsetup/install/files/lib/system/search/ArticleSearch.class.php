<?php
namespace wcf\system\search;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\SearchResultArticleContent;
use wcf\data\article\content\SearchResultArticleContentList;
use wcf\data\article\Article;
use wcf\data\category\CategoryNodeTree;
use wcf\form\IForm;
use wcf\form\SearchForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * An implementation of ISearchableObjectType for searching in articles.
 *
 * @author      Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 * @since	3.0
 */
class ArticleSearch extends AbstractSearchableObjectType {
	/**
	 * ids of the selected categories
	 * @var	integer[]
	 */
	public $articleCategoryIDs = [];
	
	/**
	 * message data cache
	 * @var	SearchResultArticleContent[]
	 */
	public $messageCache = [];
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs, array $additionalData = null) {
		$list = new SearchResultArticleContentList();
		$list->setObjectIDs($objectIDs);
		$list->readObjects();
		foreach ($list->getObjects() as $content) {
			$this->messageCache[$content->articleContentID] = $content;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObject($objectID) {
		if (isset($this->messageCache[$objectID])) return $this->messageCache[$objectID];
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTableName() {
		return 'wcf'.WCF_N.'_article_content';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIDFieldName() {
		return $this->getTableName().'.articleContentID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubjectFieldName() {
		return $this->getTableName().'.title';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsernameFieldName() {
		return 'wcf'.WCF_N.'_article.username';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTimeFieldName() {
		return 'wcf'.WCF_N.'_article.time';
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getConditions(IForm $form = null) {
		// accessible category ids
		if (isset($_POST['articleCategoryIDs'])) $this->articleCategoryIDs = ArrayUtil::toIntegerArray($_POST['articleCategoryIDs']);
		$accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();
		if (!empty($this->articleCategoryIDs)) {
			$this->articleCategoryIDs = array_intersect($accessibleCategoryIDs, $this->articleCategoryIDs);
		}
		else {
			$this->articleCategoryIDs = $accessibleCategoryIDs;
		}
		if (empty($this->articleCategoryIDs)) {
			throw new PermissionDeniedException();
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('wcf'.WCF_N.'_article.categoryID IN (?) AND wcf'.WCF_N.'_article.publicationStatus = ?', [$this->articleCategoryIDs, Article::PUBLISHED]);
		
		return $conditionBuilder;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getJoins() {
		return 'INNER JOIN wcf'.WCF_N.'_article ON (wcf'.WCF_N.'_article.articleID = '.$this->getTableName().'.articleID)';
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getFormTemplateName() {
		return 'searchArticle';
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getAdditionalData() {
		return ['articleCategoryIDs' => $this->articleCategoryIDs];
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isAccessible() {
		return MODULE_ARTICLE;
	}
	
	/**
	 * @inheritDoc
	 */
	public function show(IForm $form = null) {
		/** @var SearchForm $form */
		
		$categoryTree = new CategoryNodeTree('com.woltlab.wcf.article.category');
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		
		// get existing values
		if ($form !== null && isset($form->searchData['additionalData']['com.woltlab.wcf.article'])) {
			$this->articleCategoryIDs = $form->searchData['additionalData']['com.woltlab.wcf.article']['articleCategoryIDs'];
		}
		
		WCF::getTPL()->assign([
			'articleCategoryIDs' => $this->articleCategoryIDs,
			'articleCategoryList' => $categoryList
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.0
	 */
	public function setLocation() {
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.ArticleList');
	}
}

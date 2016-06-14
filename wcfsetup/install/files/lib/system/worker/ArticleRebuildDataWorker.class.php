<?php
namespace wcf\system\worker;
use wcf\data\article\content\ArticleContentList;
use wcf\data\article\ArticleEditor;
use wcf\data\article\ArticleList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Worker implementation for updating articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @since	3.0
 */
class ArticleRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = ArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'article.articleID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset search index
			SearchIndexManager::getInstance()->reset('com.woltlab.wcf.article');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		$articles = $this->objectList->getObjects();
		
		$commentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.articleComment');
		$sql = "SELECT	COUNT(*) AS comments, SUM(responses) AS responses
			FROM	wcf".WCF_N."_comment
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$commentStatement = WCF::getDB()->prepareStatement($sql);
		$comments = [];
		
		// update article content
		$articleContentList = new ArticleContentList();
		$articleContentList->getConditionBuilder()->add('article_content.articleID IN (?)', [$this->objectList->getObjectIDs()]);
		$articleContentList->readObjects();
		foreach ($articleContentList as $articleContent) {
			// count comments
			$commentStatement->execute([$commentObjectType->objectTypeID, $articleContent->articleContentID]);
			$row = $commentStatement->fetchSingleRow();
			if (!isset($comments[$articleContent->articleID])) $comments[$articleContent->articleID] = 0;
			$comments[$articleContent->articleID] += $row['comments'] + $row['responses'];
			
			// update search index
			SearchIndexManager::getInstance()->add('com.woltlab.wcf.article', $articleContent->articleContentID, $articleContent->content, $articleContent->title, $articles[$articleContent->articleID]->time, $articles[$articleContent->articleID]->userID, $articles[$articleContent->articleID]->username, $articleContent->languageID, $articleContent->teaser);
		}
		
		// fetch cumulative likes
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.likeableArticle')]);
		$conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
		
		$sql = "SELECT	objectID, cumulativeLikes
			FROM	wcf".WCF_N."_like_object
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$cumulativeLikes = [];
		
		/** @noinspection PhpAssignmentInConditionInspection */
		while ($row = $statement->fetchArray()) {
			$cumulativeLikes[$row['objectID']] = $row['cumulativeLikes'];
		}
		
		foreach ($this->objectList as $article) {
			$editor = new ArticleEditor($article);
			$data = [];
			
			// update cumulative likes
			$data['cumulativeLikes'] = (isset($cumulativeLikes[$article->articleID])) ? $cumulativeLikes[$article->articleID] : 0;
			
			// update comment counter
			$data['comments'] = (isset($comments[$article->articleID])) ? $comments[$article->articleID] : 0;
			
			// update data
			$editor->update($data);
		}
	}
}

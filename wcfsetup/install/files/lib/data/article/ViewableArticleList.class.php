<?php
namespace wcf\data\article;
use wcf\data\article\content\ViewableArticleContentList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\like\LikeHandler;
use wcf\system\WCF;

/**
 * Represents a list of articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
 */
class ViewableArticleList extends ArticleList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableArticle::class;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		// get like status
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "like_object.likes, like_object.dislikes";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_like_object like_object ON (like_object.objectTypeID = ".LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.likeableArticle')->objectTypeID." AND like_object.objectID = article.articleID)";
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		$userIDs = [];
		foreach ($this->getObjects() as $article) {
			if ($article->userID) {
				$userIDs[] = $article->userID;
			}
		}
		
		// cache user profiles
		if (!empty($userIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
		}
		
		// get article content
		if (!empty($this->objectIDs)) {
			$contentList = new ViewableArticleContentList();
			$contentList->getConditionBuilder()->add('article_content.articleID IN (?)', [$this->objectIDs]);
			$contentList->getConditionBuilder()->add('(article_content.languageID IS NULL OR article_content.languageID = ?)', [WCF::getLanguage()->languageID]);
			$contentList->readObjects();
			foreach ($contentList as $articleContent) {
				$this->objects[$articleContent->articleID]->setArticleContent($articleContent);
			}
		}
	}
}

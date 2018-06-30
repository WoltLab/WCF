<?php
declare(strict_types=1);
namespace wcf\data\article;
use wcf\data\article\content\ViewableArticleContentList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\like\LikeHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 * 
 * @method	ViewableArticle		current()
 * @method	ViewableArticle[]	getObjects()
 * @method	ViewableArticle|null	search($objectID)
 * @property	ViewableArticle[]	$objects
 */
class ViewableArticleList extends ArticleList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableArticle::class;
	
	/**
	 * enables/disables the loading of article content objects
	 * @var	boolean
	 */
	protected $contentLoading = true;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		if (WCF::getUser()->userID != 0) {
			// last visit time
			if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
			$this->sqlSelects .= 'tracked_visit.visitTime';
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article')." AND tracked_visit.objectID = article.articleID AND tracked_visit.userID = ".WCF::getUser()->userID.")";
		}
		
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
		
		$userIDs = $articleIDs = [];
		foreach ($this->getObjects() as $article) {
			if ($article->userID) {
				$userIDs[] = $article->userID;
			}
			if ($article->hasLabels) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		// cache user profiles
		if (!empty($userIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
		}
		
		// get article content
		if ($this->contentLoading && !empty($this->objectIDs)) {
			$contentList = new ViewableArticleContentList();
			$contentList->getConditionBuilder()->add('article_content.articleID IN (?)', [$this->objectIDs]);
			$contentList->getConditionBuilder()->add('(article_content.languageID IS NULL OR article_content.languageID = ?)', [WCF::getLanguage()->languageID]);
			$contentList->readObjects();
			foreach ($contentList as $articleContent) {
				$article = $this->objects[$articleContent->articleID];
				$article->setArticleContent($articleContent);
				
				// Some providers do pre-populate internal caches in order to retrieve the data
				// for many objects in a single step.
				$article->getDiscussionProvider();
			}
		}
		
		// get labels
		if (!empty($articleIDs)) {
			$assignedLabels = ArticleLabelObjectHandler::getInstance()->getAssignedLabels($articleIDs);
			foreach ($assignedLabels as $articleID => $labels) {
				foreach ($labels as $label) {
					$this->objects[$articleID]->addLabel($label);
				}
			}
		}
	}
	
	/**
	 * Enables/disables the loading of article content objects.
	 *
	 * @param	boolean		$enable
	 */
	public function enableContentLoading($enable = true) {
		$this->contentLoading = $enable;
	}
}

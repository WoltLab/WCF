<?php
namespace wcf\page;
use wcf\data\article\CategoryArticleList;
use wcf\data\article\ViewableArticle;
use wcf\data\comment\StructuredCommentList;
use wcf\data\like\object\LikeObject;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\CommentHandler;
use wcf\system\reaction\ReactionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\MetaTagHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a cms article.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class ArticlePage extends AbstractArticlePage {
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
	 * like data for the article
	 * @var	LikeObject[]
	 */
	public $articleLikeData = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = $this->articleContent->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get comments
		if ($this->article->enableComments) {
			$this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.articleComment');
			$this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
			$this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->articleContent->articleContentID);
		}
		
		// get next article
		$articleList = new CategoryArticleList($this->article->categoryID);
		$articleList->getConditionBuilder()->add('article.time ' . (ARTICLE_SORT_ORDER == 'DESC' ? '>' : '<') . ' ?', [$this->article->time]);
		$articleList->sqlOrderBy = 'article.time ' . (ARTICLE_SORT_ORDER == 'DESC' ? 'ASC' : 'DESC');
		$articleList->sqlLimit = 1;
		$articleList->readObjects();
		foreach ($articleList as $article) $this->nextArticle = $article;
		
		// get previous article
		$articleList = new CategoryArticleList($this->article->categoryID);
		$articleList->getConditionBuilder()->add('article.time ' . (ARTICLE_SORT_ORDER == 'DESC' ? '<' : '>') . ' ?', [$this->article->time]);
		$articleList->sqlOrderBy = 'article.time ' . ARTICLE_SORT_ORDER;
		$articleList->sqlLimit = 1;
		$articleList->readObjects();
		foreach ($articleList as $article) $this->previousArticle = $article;
		
		// fetch likes
		if (MODULE_LIKE) {
			$objectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.likeableArticle');
			ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->article->articleID]);
			$this->articleLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
		}
		
		// add meta/og tags
		MetaTagHandler::getInstance()->addTag('og:title', 'og:title', $this->articleContent->getTitle() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE), true);
		MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('Article', ['object' => $this->articleContent]), true);
		MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
		MetaTagHandler::getInstance()->addTag('og:description', 'og:description', ($this->articleContent->teaser ?: StringUtil::decodeHTML(StringUtil::stripHTML($this->articleContent->getFormattedTeaser()))), true);
		
		if ($this->articleContent->getTeaserImage()) {
			MetaTagHandler::getInstance()->addTag('og:image', 'og:image', $this->articleContent->getTeaserImage()->getLink(), true);
			
			if ($this->articleContent->getImage()) {
				MetaTagHandler::getInstance()->addTag('og:image:width', 'og:image:width', $this->articleContent->getImage()->width, true);
				MetaTagHandler::getInstance()->addTag('og:image:height', 'og:image:height', $this->articleContent->getImage()->height, true);
			}
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
			'previousArticle' => $this->previousArticle,
			'nextArticle' => $this->nextArticle,
			'articleLikeData' => $this->articleLikeData,
			
			// nullified values for backwards-compatibility
			'commentCanAdd' => 0,
			'commentList' => null,
			'commentObjectTypeID' => 0,
			'lastCommentTime' => 0,
			'likeData' => [],
		]);
	}
}

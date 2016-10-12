<?php
namespace wcf\page;
use wcf\data\article\CategoryArticleList;
use wcf\data\article\ViewableArticle;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the amp version of an article.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class ArticleAmpPage extends AbstractArticlePage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'ampArticle';
	
	/**
	 * list of additional articles
	 * @var ViewableArticle[]
	 */
	public $additionalArticles;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('ArticleAmp', ['object' => $this->articleContent]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get next/previous articles
		$nextArticleList = new CategoryArticleList($this->article->categoryID);
		$nextArticleList->getConditionBuilder()->add('article.time > ?', [$this->article->time]);
		$nextArticleList->sqlOrderBy = 'article.time';
		$nextArticleList->sqlLimit = 3;
		$nextArticleList->readObjects();
		$previousArticleList = new CategoryArticleList($this->article->categoryID);
		$previousArticleList->getConditionBuilder()->add('article.time < ?', [$this->article->time]);
		$previousArticleList->sqlOrderBy = 'article.time DESC';
		$previousArticleList->sqlLimit = 3;
		$previousArticleList->readObjects();
		$this->additionalArticles = array_merge($nextArticleList->getObjects(), $previousArticleList->getObjects());
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'regularCanonicalURL' => $this->articleContent->getLink(),
			'additionalArticles' => $this->additionalArticles
		]);
	}
}

<?php
namespace wcf\system\page\handler;
use wcf\data\article\ViewableArticleList;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\WCF;

/**
 * Menu page handler for the article page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
class ArticlePageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return ViewableArticleRuntimeCache::getInstance()->getObject($objectID)->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return ViewableArticleRuntimeCache::getInstance()->getObject($objectID) !== null;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		$article = ViewableArticleRuntimeCache::getInstance()->getObject($objectID);
		
		return ($article !== null && $article->canRead());
	}
	
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		$articleList = new ViewableArticleList();
		$articleList->sqlSelects = "(SELECT title FROM wcf".WCF_N."_article_content WHERE articleID = article.articleID AND (languageID IS NULL OR languageID = ".WCF::getLanguage()->languageID.") LIMIT 1) AS title";
		$articleList->getConditionBuilder()->add('article.articleID IN (SELECT articleID FROM wcf'.WCF_N.'_article_content WHERE title LIKE ?)', ['%' . $searchString . '%']);
		$articleList->sqlLimit = 10;
		$articleList->sqlOrderBy = 'title';
		$articleList->readObjects();
		
		$results = [];
		foreach ($articleList->getObjects() as $article) {
			$results[] = [
				'description' => $article->getFormattedTeaser(),
				'image' => $article->getImage() ? $article->getImage()->getElementTag(48) : '',
				'link' => $article->getLink(),
				'objectID' => $article->articleID,
				'title' => $article->getTitle()
			];
		}
		
		return $results;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->pageObjectID === null) {
			return '';
		}
		
		$article = ViewableArticleRuntimeCache::getInstance()->getObject($user->pageObjectID);
		if ($article === null || !$article->canRead()) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['article' => $article]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepareOnlineLocation(/** @noinspection PhpUnusedParameterInspection */Page $page, UserOnline $user) {
		if ($user->pageObjectID !== null) {
			ViewableArticleRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
		}
	}
}

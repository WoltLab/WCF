<?php
namespace wcf\system\page\handler;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\WCF;

/**
 * Menu page handler for the article page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
		return ViewableArticleRuntimeCache::getInstance()->getObject($objectID)->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		// @todo
		return [];
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

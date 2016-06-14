<?php
namespace wcf\system\page\handler;
use wcf\data\article\category\ArticleCategory;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Menu page handler for the category article list page.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
class CategoryArticleListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return LinkHandler::getInstance()->getLink('CategoryArticleList', [
			'object' => ArticleCategory::getCategory($objectID),
			'forceFrontend' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return ArticleCategory::getCategory($objectID) !== null;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return ArticleCategory::getCategory($objectID)->isAccessible();
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
		
		$category = ArticleCategory::getCategory($user->pageObjectID);
		if ($category === null || !$category->isAccessible()) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['category' => $category]);
	}
}

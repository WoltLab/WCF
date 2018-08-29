<?php
namespace wcf\system\page\handler;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\ViewableArticle;

/**
 * Page handler implementation for the page showing the list of articles in watched categories.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.2
 */
class WatchedArticleListPageHandler extends AbstractMenuPageHandler {
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount(/** @noinspection PhpUnusedParameterInspection */$objectID = null) {
		return ARTICLE_ENABLE_VISIT_TRACKING ? ViewableArticle::getWatchedUnreadArticles() : 0;
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return !empty(ArticleCategory::getSubscribedCategoryIDs());
	}
}

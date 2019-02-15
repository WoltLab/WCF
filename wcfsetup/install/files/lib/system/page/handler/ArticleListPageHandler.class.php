<?php
namespace wcf\system\page\handler;
use wcf\data\article\ViewableArticle;

/**
 * Page handler implementation for the page showing the list of articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.1
 */
class ArticleListPageHandler extends AbstractMenuPageHandler {
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount(/** @noinspection PhpUnusedParameterInspection */$objectID = null) {
		return ARTICLE_ENABLE_VISIT_TRACKING ? ViewableArticle::getUnreadArticles() : 0;
	}
}

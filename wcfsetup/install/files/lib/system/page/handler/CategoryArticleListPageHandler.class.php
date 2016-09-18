<?php
namespace wcf\system\page\handler;
use wcf\data\article\category\ArticleCategory;
use wcf\system\request\LinkHandler;

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
	use TDecoratedCategoryOnlineLocationLookupPageHandler;
	
	/**
	 * @see	TDecoratedCategoryLookupPageHandler::getDecoratedCategoryClass()
	 */
	protected function getDecoratedCategoryClass() {
		return ArticleCategory::class;
	}
}

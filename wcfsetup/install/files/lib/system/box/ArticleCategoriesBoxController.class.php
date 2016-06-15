<?php
namespace wcf\system\box;
use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\page\ArticlePage;
use wcf\page\CategoryArticleListPage;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for article categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class ArticleCategoriesBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'footer'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get categories
		$categoryTree = new ArticleCategoryNodeTree('com.woltlab.wcf.article.category');
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		
		if (iterator_count($categoryList)) {
			// get active category
			$activeCategory = null;
			if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof CategoryArticleListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof ArticlePage) {
				if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
					$activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
				}
			}
			
			$this->content = WCF::getTPL()->fetch('boxArticleCategories', 'wcf', ['categoryList' => $categoryList, 'activeCategory' => $activeCategory]);
		}	
	}
}

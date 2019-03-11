<?php
namespace wcf\system\box;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\page\TrophyListPage;
use wcf\page\TrophyPage;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for trophy categories.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	5.2
 */
class TrophyCategoriesBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'footer'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$categories = TrophyCategoryCache::getInstance()->getEnabledCategories();
		
		if (count($categories)) {
			// get active category
			$activeCategory = null;
			if (RequestHandler::getInstance()->getActiveRequest() !== null) {
				if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof TrophyListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof TrophyPage) {
					if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
						$activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
					}
				}
			}
			
			$this->content = WCF::getTPL()->fetch('boxTrophyCategories', 'wcf', ['categories' => $categories, 'activeCategory' => $activeCategory], true);
		}
	}
}

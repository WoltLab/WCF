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
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TrophyCategoriesBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'footerBoxes',
        'sidebarLeft',
        'sidebarRight',
        'contentTop',
        'contentBottom',
        'footer',
    ];

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $categories = TrophyCategoryCache::getInstance()->getEnabledCategories();

        if (\count($categories)) {
            // get active category
            $activeCategory = null;
            $requestObject = RequestHandler::getInstance()->getActiveRequest()?->getRequestObject();
            if ($requestObject instanceof TrophyListPage || $requestObject instanceof TrophyPage) {
                if ($requestObject->category !== null) {
                    $activeCategory = $requestObject->category;
                }
            }

            $this->content = WCF::getTPL()->render(
                'wcf',
                'boxTrophyCategories',
                ['categories' => $categories, 'activeCategory' => $activeCategory]
            );
        }
    }
}

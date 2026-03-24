<?php

namespace wcf\system\page\handler;

use wcf\data\trophy\category\TrophyCategory;

/**
 * Menu page handler for the trophy list page.
 *
 * @author  Joshua Rüsweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyListPageHandler extends AbstractLookupPageHandler
{
    use TDecoratedCategoryOnlineLocationLookupPageHandler;

    /**
     * @return string
     */
    #[\Override]
    protected function getDecoratedCategoryClass()
    {
        return TrophyCategory::class;
    }
}

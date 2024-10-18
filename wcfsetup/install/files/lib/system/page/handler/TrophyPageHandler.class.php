<?php

namespace wcf\system\page\handler;

use wcf\data\trophy\TrophyCache;
use wcf\data\trophy\TrophyList;
use wcf\system\WCF;

/**
 * Menu page handler for the trophy page.
 *
 * @author  Joshua Rüsweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class TrophyPageHandler extends AbstractLookupPageHandler
{
    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return TrophyCache::getInstance()->getTrophyByID($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        return TrophyCache::getInstance()->getTrophyByID($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return WCF::getSession()->getPermission('user.profile.trophy.canSeeTrophies');
    }

    /**
     * @inheritDoc
     */
    public function lookup($searchString)
    {
        $trophyList = new TrophyList();
        if (!empty($trophyList->sqlJoins)) {
            $trophyList->sqlJoins .= ', ';
        }
        $trophyList->sqlJoins = "
            LEFT JOIN   wcf1_language_item language_item
            ON          language_item.languageItem = trophy.title";
        $trophyList->getConditionBuilder()->add(
            '(trophy.title LIKE ? OR language_item.languageItemValue LIKE ?)',
            ['%' . $searchString . '%', '%' . $searchString . '%']
        );
        $trophyList->sqlLimit = 10;
        $trophyList->sqlOrderBy = 'title';
        $trophyList->readObjects();

        $results = [];
        foreach ($trophyList->getObjects() as $trophy) {
            $results[] = [
                'description' => $trophy->getDescription(),
                'image' => $trophy->renderTrophy(48),
                'link' => $trophy->getLink(),
                'objectID' => $trophy->trophyID,
                'title' => $trophy->getTitle(),
            ];
        }

        return $results;
    }
}

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
 */
class TrophyPageHandler extends AbstractLookupPageHandler
{
    #[\Override]
    public function getLink(int $objectID)
    {
        return TrophyCache::getInstance()->getTrophyByID($objectID)->getLink();
    }

    #[\Override]
    public function isValid(?int $objectID)
    {
        return TrophyCache::getInstance()->getTrophyByID($objectID) !== null;
    }

    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        return WCF::getSession()->getPermission('user.profile.trophy.canSeeTrophies');
    }

    #[\Override]
    public function lookup(string $searchString)
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

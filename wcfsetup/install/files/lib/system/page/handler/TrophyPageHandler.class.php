<?php

namespace wcf\system\page\handler;

use wcf\data\trophy\TrophyCache;
use wcf\data\trophy\L10nTrophyList;
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
        return WCF::getSession()->hasPermission('user.profile.trophy.canSeeTrophies');
    }

    #[\Override]
    public function lookup(string $searchString)
    {
        $trophyList = new L10nTrophyList();
        $trophyList->getConditionBuilder()->add(
            "trophy.trophyID IN (
                SELECT  trophyID
                FROM    wcf1_trophy_l10n
                WHERE   (languageID = ? OR languageID IS NULL)
                    AND title LIKE ?
            )",
            [
                WCF::getLanguage()->languageID,
                '%' . WCF::getDB()->escapeLikeValue($searchString) . '%',
            ]
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

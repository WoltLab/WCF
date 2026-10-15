<?php

namespace wcf\system\search\acp;

use wcf\acp\form\TrophyEditForm;
use wcf\data\trophy\L10nTrophyList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for trophies.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyACPSearchResultProvider implements IACPSearchResultProvider
{
    #[\Override]
    public function search(string $query)
    {
        if (\MODULE_TROPHY === 0 || !WCF::getSession()->hasPermission('admin.trophy.canManageTrophy')) {
            return [];
        }

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
                '%' . WCF::getDB()->escapeLikeValue($query) . '%',
            ]
        );
        $trophyList->sqlOrderBy = 'title';
        $trophyList->readObjects();

        $results = [];
        foreach ($trophyList as $trophy) {
            $results[] = new ACPSearchResult($trophy->getTitle(), LinkHandler::getInstance()->getControllerLink(TrophyEditForm::class, [
                'id' => $trophy->trophyID,
            ]));
        }

        return $results;
    }
}

<?php

namespace wcf\system\search\acp;

use wcf\data\trophy\Trophy;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for trophies.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyACPSearchResultProvider implements IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    public function search($query)
    {
        if (!MODULE_TROPHY || !WCF::getSession()->getPermission('admin.trophy.canManageTrophy')) {
            return [];
        }

        $results = [];

        // search by language item
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
        $conditions->add("languageItem LIKE ?", ['wcf.user.trophy.title%']);
        $conditions->add("languageItemValue LIKE ?", ['%' . $query . '%']);

        $sql = "SELECT  languageItem
                FROM    wcf1_language_item
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $trophyIDs = [];
        while ($row = $statement->fetchArray()) {
            $trophyIDs[] = \str_replace('wcf.user.trophy.title', '', $row['languageItem']);
        }

        $conditions = new PreparedStatementConditionBuilder(false);
        if (!empty($trophyIDs)) {
            $conditions->add("trophyID IN (?)", [$trophyIDs]);
        }

        $sql = "SELECT  *
                FROM    wcf1_trophy
                WHERE   title LIKE ?
                    " . (!empty($conditions->getParameters()) ? "OR " . $conditions : "");
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([
            '%' . $query . '%',
        ], $conditions->getParameters()));

        /** @var Trophy $trophy */
        while ($trophy = $statement->fetchObject(Trophy::class)) {
            $results[] = new ACPSearchResult($trophy->getTitle(), LinkHandler::getInstance()->getLink('TrophyEdit', [
                'id' => $trophy->trophyID,
            ]));
        }

        return $results;
    }
}

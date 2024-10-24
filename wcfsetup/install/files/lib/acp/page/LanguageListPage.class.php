<?php

namespace wcf\acp\page;

use wcf\data\language\LanguageList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all installed languages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    LanguageList $objectList
 */
class LanguageListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.language.list';

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'languageName';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.language.canManageLanguage'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = LanguageList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['languageID', 'languageCode', 'languageName', 'users', 'variables', 'customVariables'];

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = "(
            SELECT  COUNT(*)
            FROM    wcf1_user user
            WHERE   languageID = language.languageID
        ) AS users, (
            SELECT  COUNT(*)
            FROM    wcf1_language_item
            WHERE   languageID = language.languageID
        ) AS variables, (
            SELECT  COUNT(*)
            FROM    wcf1_language_item
            WHERE   languageID = language.languageID
                AND languageCustomItemValue IS NOT NULL
        ) AS customVariables";
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'languages' => $this->objectList->getObjects(),
        ]);
    }
}

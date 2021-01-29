<?php

/**
 * Second step of the updates to the `wcf1_tag_to_object` table.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

$tables = [
    PartialDatabaseTable::create('wcf1_tag_to_object')
        ->indices([
            DatabaseTableIndex::create()
                ->columns(['objectTypeID', 'languageID', 'tagID'])
                ->drop(),
        ]),
];

(new DatabaseTableChangeProcessor(
    /** @var ScriptPackageInstallationPlugin $this */
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
))->process();

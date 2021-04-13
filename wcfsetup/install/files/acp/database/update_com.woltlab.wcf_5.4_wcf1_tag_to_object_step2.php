<?php

/**
 * Second step of the updates to the `wcf1_tag_to_object` table.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_tag_to_object')
        ->indices([
            DatabaseTableIndex::create()
                ->columns(['objectTypeID', 'languageID', 'tagID'])
                ->drop(),
        ]),
];

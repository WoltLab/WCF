<?php

/**
 * Adjusts wcf1_acp_session_access_log and wcf1_session.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\column\MediumblobDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_acp_session_access_log')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('requestMethod')
                ->defaultValue(''),
        ]),
    PartialDatabaseTable::create('wcf1_session')
        ->columns([
            MediumblobDatabaseTableColumn::create('sessionVariables')->drop(),
        ]),
];

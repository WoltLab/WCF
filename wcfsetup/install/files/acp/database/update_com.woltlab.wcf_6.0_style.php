<?php

/**
 * Updates the database layout for the styles during the update from 5.5 to 6.0.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_style')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('hasDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_style_variable')
        ->columns([
            MediumtextDatabaseTableColumn::create('defaultValueDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_style_variable_value')
        ->columns([
            MediumtextDatabaseTableColumn::create('variableValueDarkMode'),
        ]),
];

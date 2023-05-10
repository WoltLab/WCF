<?php

/**
 * Marks the first time setup as completed during the upgrade.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\option\Option;
use wcf\data\option\OptionAction;

$objectAction = new OptionAction(
    [],
    'updateAll',
    [
        'data' => [
            Option::getOptionByName('first_time_setup_state')->optionID => -1,
        ],
    ]
);
$objectAction->executeAction();

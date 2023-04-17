<?php

namespace wcf\acp\page;

use wcf\data\option\Option;
use wcf\data\option\OptionAction;
use wcf\page\AbstractPage;

/**
 * Shows a success page for the completed first time setup.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class FirstTimeSetupCompletedPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.general.canUseAcp'];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (\FIRST_TIME_SETUP_STATE != -1) {
            $objectAction = new OptionAction(
                [],
                'updateAll',
                [
                    'data' => [
                        Option::getOptionByName('offline')->optionID => 0,
                        Option::getOptionByName('first_time_setup_state')->optionID => -1,
                    ],
                ]
            );
            $objectAction->executeAction();
        }
    }
}

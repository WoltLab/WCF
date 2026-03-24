<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\trophy\condition\TrophyConditionHandler;

/**
 * Assigns automatically trophies.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AssignTrophiesCronjob extends AbstractCronjob
{
    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        if (MODULE_TROPHY) {
            TrophyConditionHandler::getInstance()->revokeTrophies(100);
            TrophyConditionHandler::getInstance()->assignTrophies(100);
        }
    }
}

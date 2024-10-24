<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserAction;
use wcf\system\WCF;

/**
 * Deletes canceled user accounts.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserQuitCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $sql = "SELECT  userID
                FROM    wcf1_user
                WHERE   quitStarted > ?
                    AND quitStarted < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            0,
            TIME_NOW - 7 * 24 * 3600,
        ]);
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($userIDs)) {
            $action = new UserAction($userIDs, 'delete');
            $action->executeAction();
        }
    }
}

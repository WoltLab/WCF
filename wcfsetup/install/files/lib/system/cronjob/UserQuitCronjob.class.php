<?php

namespace wcf\system\cronjob;

use wcf\command\user\DeleteUser;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\runtime\UserRuntimeCache;
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
    #[\Override]
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
            \TIME_NOW - 7 * 24 * 3600,
        ]);
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        foreach (UserRuntimeCache::getInstance()->getObjects($userIDs) as $user) {
            new DeleteUser($user)();
        }
    }
}

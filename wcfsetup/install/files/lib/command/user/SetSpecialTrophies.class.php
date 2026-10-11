<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Sets the special trophies of a user, replacing the existing ones.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetSpecialTrophies
{
    /**
     * @param list<int> $trophyIDs
     */
    public function __construct(
        private readonly User $user,
        private readonly array $trophyIDs,
    ) {}

    public function __invoke(): void
    {
        WCF::getDB()->beginTransaction();

        $sql = "DELETE FROM wcf1_user_special_trophy
                WHERE       userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->user->userID]);

        $sql = "INSERT INTO wcf1_user_special_trophy
                            (userID, trophyID)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($this->trophyIDs as $trophyID) {
            $statement->execute([
                $this->user->userID,
                $trophyID,
            ]);
        }

        WCF::getDB()->commitTransaction();

        UserStorageHandler::getInstance()->reset([$this->user->userID], 'specialTrophies');
    }
}

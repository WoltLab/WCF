<?php

namespace wcf\command\trophy;

use wcf\command\file\DeleteFiles;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyList;
use wcf\event\trophy\TrophyDeleted;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Deletes a trophy, including the trophies awarded to users.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteTrophy
{
    public function __construct(
        private readonly Trophy $trophy,
    ) {}

    public function __invoke(): void
    {
        $this->deleteUserTrophies();

        TrophyBuilder::delete($this->trophy);

        // The image is deleted only after the trophy, because deleting a file cannot be rolled back.
        if ($this->trophy->iconFileID !== null) {
            $file = FileRuntimeCache::getInstance()->getObject($this->trophy->iconFileID);
            if ($file !== null) {
                new DeleteFiles([$file])();
            }
        }

        TrophyBuilder::resetCache();
        UserStorageHandler::getInstance()->resetAll('specialTrophies');

        EventHandler::getInstance()->fire(new TrophyDeleted($this->trophy));
    }

    /**
     * Deletes the awarded trophies to update the trophy points of the users.
     * The trophies of disabled trophies or categories do not count towards the
     * trophy points and are deleted by the foreign key.
     */
    private function deleteUserTrophies(): void
    {
        $userTrophyList = new UserTrophyList();
        if ($userTrophyList->sqlJoins !== '') {
            $userTrophyList->sqlJoins .= ' ';
        }
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $userTrophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('user_trophy.trophyID = ?', [$this->trophy->trophyID]);
        $userTrophyList->readObjects();

        if ($userTrophyList->getObjects() !== []) {
            (new UserTrophyAction($userTrophyList->getObjects(), 'delete'))->executeAction();
        }
    }
}

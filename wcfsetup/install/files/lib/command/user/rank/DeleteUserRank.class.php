<?php

namespace wcf\command\user\rank;

use wcf\command\file\DeleteFiles;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\event\user\rank\UserRankDeleted;
use wcf\system\cache\builder\UserRankCacheBuilder;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\event\EventHandler;

/**
 * Deletes a user rank.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteUserRank
{
    public function __construct(
        private readonly UserRank $rank,
    ) {}

    public function __invoke(): void
    {
        UserRankBuilder::delete($this->rank);

        // The image is deleted only after the rank, because deleting a file
        // cannot be rolled back.
        if ($this->rank->rankImageFileID !== null) {
            $file = FileRuntimeCache::getInstance()->getObject($this->rank->rankImageFileID);
            if ($file !== null) {
                new DeleteFiles([$file])();
            }
        }

        UserRankCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserRankDeleted($this->rank));
    }
}

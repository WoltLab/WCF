<?php

namespace wcf\system\cache\builder;

use wcf\data\file\FileList;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankList;

/**
 * Caches the list of user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserRankCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        $list = new UserRankList();
        $list->readObjects();
        $ranks = $list->getObjects();

        $fileIDs = [];
        foreach ($ranks as $rank) {
            if ($rank->rankImageFileID !== null) {
                $fileIDs[] = $rank->rankImageFileID;
            }
        }

        if ($fileIDs !== []) {
            $fileList = new FileList();
            $fileList->setObjectIDs($fileIDs);
            $fileList->readObjects();

            foreach ($ranks as $rank) {
                if ($rank->rankImageFileID === null) {
                    continue;
                }

                $file = $fileList->search($rank->rankImageFileID);
                if ($file !== null) {
                    $rank->setImageFile($file);
                }
            }
        }

        return $ranks;
    }

    public function getRank(int $rankID): ?UserRank
    {
        return $this->getData()[$rankID] ?? null;
    }
}

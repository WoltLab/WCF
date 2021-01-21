<?php

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankEditor;
use wcf\data\user\rank\UserRankList;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\DownloadRankImageJob;
use wcf\util\Url;

/**
 * Downloads the rank image files to the new internal upload system.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

$rankList = new UserRankList();
$rankList->readObjects();

foreach ($rankList as $rank) {
    if (!empty($rank->rankImage)) {
        if (Url::is($rank->rankImage)) {
            BackgroundQueueHandler::getInstance()->enqueueIn(new DownloadRankImageJob($rank), 60);
            continue;
        }

        $rankEditor = new UserRankEditor($rank);

        if (\file_exists(WCF_DIR . $rank->rankImage)) {
            $newImageName = WCF_DIR . UserRank::RANK_IMAGE_DIR . $rank->rankID . '-' . \basename($rank->rankImage);
            \copy(WCF_DIR . $rank->rankImage, $newImageName);

            $rankEditor->update([
                'rankImage' => \basename($newImageName),
            ]);
        } else {
            $rankEditor->update([
                'rankImage' => "",
            ]);
        }
    }
}

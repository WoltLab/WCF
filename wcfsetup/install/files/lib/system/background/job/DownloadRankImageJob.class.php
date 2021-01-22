<?php

namespace wcf\system\background\job;

use GuzzleHttp\Psr7\Request;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankEditor;
use wcf\system\io\HttpFactory;
use wcf\util\Url;

/**
 * Downloads the rank images and stores it locally within the rank image path.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Background\Job
 * @since   5.4
 * @deprecated  5.4 - This background job is used for the upgrade from 5.3 to 5.4 and will be removed with 5.5.
 */
class DownloadRankImageJob extends AbstractBackgroundJob
{
    /**
     * @inheritDoc
     */
    const MAX_FAILURES = 5;

    /**
     * @var int
     */
    protected $userRankID;

    public function __construct(UserRank $userRank)
    {
        $this->userRankID = $userRank->rankID;
    }

    /**
     * @return  int every 10 minutes
     */
    public function retryAfter()
    {
        return 10 * 60;
    }

    /**
     * @inheritDoc
     */
    public function perform()
    {
        $rank = new UserRank($this->userRankID);
        if (!$rank->rankID) {
            return;
        }
        if (empty($rank->rankImage)) {
            return;
        }
        if (!Url::is($rank->rankImage)) {
            return;
        }

        $rankEditor = new UserRankEditor($rank);

        $extension = \pathinfo(Url::parse($rank->rankImage)['path'], \PATHINFO_EXTENSION);
        if (\in_array($extension, ['gif', 'png', 'jpg', 'jpeg', 'svg', 'webp'])) {
            $http = HttpFactory::makeClient([
                'timeout' => 10,
            ]);

            $imageDest = WCF_DIR . UserRank::RANK_IMAGE_DIR . $rank->rankID . '-rankImage.' . $extension;
            $http->send(new Request('GET', $rank->rankImage), [
                'sink' => $imageDest,
            ]);

            $rankEditor->update([
                'rankImage' => \basename($imageDest),
            ]);
        } else {
            $rankEditor->update([
                'rankImage' => "",
            ]);
        }
    }
}

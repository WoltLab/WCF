<?php

namespace wcf\form;

use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * Shows the moderation report form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ModerationReportForm extends AbstractModerationForm
{
    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $processor = ModerationQueueManager::getInstance()->getProcessor(null, null, $this->queue->objectTypeID);
        if (!($processor instanceof IModerationQueueReportHandler)) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        $reportUser = UserProfileRuntimeCache::getInstance()->getObject($this->queue->userID);
        if ($reportUser === null) {
            $reportUser = UserProfile::getGuestUserProfile();
        }

        WCF::getTPL()->assign([
            'reportedContent' => ModerationQueueReportManager::getInstance()->getReportedContent($this->queue),
            'queueManager' => ModerationQueueReportManager::getInstance(),
            'reportUser' => $reportUser,
        ]);
    }
}

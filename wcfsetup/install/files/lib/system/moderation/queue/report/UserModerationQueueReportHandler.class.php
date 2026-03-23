<?php

namespace wcf\system\moderation\queue\report;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\exception\SystemException;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for user profiles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserModerationQueueReportHandler extends AbstractModerationQueueHandler implements IModerationQueueReportHandler
{
    /**
     * @inheritDoc
     */
    protected $className = User::class;

    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.report';

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.user';

    /**
     * @inheritDoc
     */
    public function assignQueues(array $queues)
    {
        $assignments = [];
        foreach ($queues as $queue) {
            $assignUser = false;
            if (WCF::getSession()->getPermission('mod.general.canUseModeration')) {
                $assignUser = true;
            }

            $assignments[$queue->queueID] = $assignUser;
        }

        ModerationQueueManager::getInstance()->setAssignment($assignments);
    }

    /**
     * @inheritDoc
     */
    public function canReport(int $objectID)
    {
        if (!$this->isValid($objectID)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getContainerID(int $objectID)
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getReportedContent(ViewableModerationQueue $queue)
    {
        $user = $queue->getAffectedObject();
        \assert($user instanceof User);

        return WCF::getTPL()->render('wcf', 'moderationUser', [
            'user' => new UserProfile($user),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getReportedObject(int $objectID)
    {
        if ($this->isValid($objectID)) {
            return $this->getUser($objectID);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isValid(int $objectID)
    {
        if ($this->getUser($objectID) === null) {
            return false;
        }

        return true;
    }

    /**
     * Returns a user object by user id or null if user id is invalid.
     *
     * @param int $objectID
     * @return  User|null
     */
    protected function getUser($objectID)
    {
        return UserRuntimeCache::getInstance()->getObject($objectID);
    }

    /**
     * @inheritDoc
     */
    public function populate(array $queues)
    {
        $objectIDs = [];
        foreach ($queues as $object) {
            $objectIDs[] = $object->objectID;
        }

        $users = UserRuntimeCache::getInstance()->getObjects($objectIDs);
        foreach ($queues as $object) {
            if ($users[$object->objectID] !== null) {
                $object->setAffectedObject($users[$object->objectID]);
            } else {
                $object->setIsOrphaned();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function canRemoveContent(ModerationQueue $queue)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeContent(ModerationQueue $queue, string $message)
    {
        throw new SystemException("it's not allowed to delete users using the moderation");
    }
}

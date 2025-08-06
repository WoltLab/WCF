<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueueList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * API Endpoint to get the moderation queue items for the user menu.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[GetRequest('/core/moderation-queues/user-menu-items')]
final class GetUserMenuModerationQueueItems implements IController
{
    public const MAX_ITEMS = 10;

    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['mod.general.canUseModeration']);

        $queueItems = $this->getModerationQueueItems();
        $unreadEntries = ModerationQueueManager::getInstance()->getUnreadModerationCount();

        if ($this->userStorageCouldBeOutdated(\count($queueItems), $unreadEntries)) {
            $this->removeOrphansQueueItems();

            $unreadEntries = ModerationQueueManager::getInstance()->getUnreadModerationCount();
        }

        $items = \array_map(static function (ViewableModerationQueue $queue) {
            return [
                'content' => StringUtil::encodeHTML($queue->getAffectedObject()->getTitle()),
                'image' => $queue->getIcon()->toHtml(48),
                'isUnread' => $queue->isNew(),
                'link' => $queue->getLink(),
                'objectId' => $queue->queueID,
                'time' => $queue->lastChangeTime,
                'usernames' => [],
            ];
        }, $queueItems);

        return new JsonResponse([
            'unreadModerationCount' => $unreadEntries,
            // Ensure that no key-value map is returned.
            'items' => \array_values($items),
        ]);
    }

    private function userStorageCouldBeOutdated(int $items, int $unreadEntries): bool
    {
        if ($items >= self::MAX_ITEMS) {
            return false;
        }

        if ($items < $unreadEntries) {
            return true;
        }

        return false;
    }

    private function removeOrphansQueueItems(): void
    {
        ModerationQueueManager::getInstance()->identifyOrphans();
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');
    }

    /**
     * @return array<int, ViewableModerationQueue>
     */
    private function getModerationQueueItems(): array
    {
        $items = $this->getUnreadModerationQueueItems();

        $count = \count($items);
        if ($count < self::MAX_ITEMS) {
            $items = \array_merge($items, $this->getQueueItems(\array_keys($items), $count));
        }

        return $items;
    }

    /**
     * @return array<int, ViewableModerationQueue>
     */
    private function getQueueItems(array $unreadQueueIDs, int $count): array
    {
        $queueList = new ViewableModerationQueueList();
        $queueList->getConditionBuilder()->add(
            "moderation_queue.status IN (?)",
            [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]
        );

        if ($unreadQueueIDs !== []) {
            $queueList->getConditionBuilder()->add("moderation_queue.queueID NOT IN (?)", [$unreadQueueIDs]);
        }

        $queueList->sqlOrderBy = "moderation_queue.lastChangeTime DESC";
        $queueList->sqlLimit = self::MAX_ITEMS - $count;
        $queueList->readObjects();

        return $queueList->getObjects();
    }

    /**
     * @return array<int, ViewableModerationQueue>
     */
    private function getUnreadModerationQueueItems(): array
    {
        $queueList = new ViewableModerationQueueList();
        $queueList->sqlJoins .= " LEFT JOIN   wcf1_tracked_visit tracked_visit
                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue') . "
                        AND tracked_visit.objectID = moderation_queue.queueID
                        AND tracked_visit.userID = " . WCF::getUser()->userID;

        $queueList->getConditionBuilder()->add("moderation_queue_to_user.userID = ?", [WCF::getUser()->userID]);
        $queueList->getConditionBuilder()->add("moderation_queue_to_user.isAffected = ?", [1]);
        $queueList->getConditionBuilder()->add(
            "moderation_queue.status IN (?)",
            [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]
        );
        $queueList->getConditionBuilder()->add(
            "moderation_queue.time > ?",
            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.moderation.queue')]
        );
        $queueList->getConditionBuilder()->add("(moderation_queue.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
        $queueList->sqlOrderBy = "moderation_queue.lastChangeTime DESC";
        $queueList->readObjects();

        return $queueList->getObjects();
    }
}

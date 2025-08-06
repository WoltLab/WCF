<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\moderation\queue\MarkModerationQueueAsRead;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Marks the moderation queue entry with the given ID as read.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/moderation-queues/{id:\d+}/mark-as-read')]
final class MarkAsRead implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = Helper::fetchObjectFromRequestParameter($variables['id'], ModerationQueue::class);

        $this->assertQueueCanBeMarkedAsRead($queue);

        (new MarkModerationQueueAsRead($queue))();

        return new JsonResponse([
            'unreadModerationItems' => ModerationQueueManager::getInstance()->getUnreadModerationCount(true)
        ]);
    }

    private function assertQueueCanBeMarkedAsRead(ModerationQueue $queue): void
    {
        if (!$queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }
}

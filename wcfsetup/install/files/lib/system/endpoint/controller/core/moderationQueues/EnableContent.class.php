<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Enables the content associated with the moderation queue entry with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/moderation-queues/{id:\d+}/enable-content')]
final class EnableContent implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = Helper::fetchObjectFromRequestParameter($variables['id'], ModerationQueue::class);

        $this->assertContentCanBeEnabled($queue);

        ModerationQueueActivationManager::getInstance()->enableContent($queue);
        $editor = new ModerationQueueEditor($queue);
        $editor->markAsConfirmed();

        return new JsonResponse([]);
    }

    private function assertContentCanBeEnabled(ModerationQueue $queue): void
    {
        if (!$queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }
}

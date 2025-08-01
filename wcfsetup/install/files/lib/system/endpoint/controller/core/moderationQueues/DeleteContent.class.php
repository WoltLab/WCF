<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\AbstractModerationQueueManager;

/**
 * Deletes the content associated with the moderation queue entry with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/moderation-queues/{id:\d+}/delete-content')]
final class DeleteContent implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = Helper::fetchObjectFromRequestParameter($variables['id'], ModerationQueue::class);

        $this->assertContentCanBeRemoved($queue);

        $parameters = Helper::mapApiParameters($request, DeleteContentParameters::class);

        $this->deleteContent($queue, $parameters->reason ?? '');

        return new JsonResponse([]);
    }

    private function assertContentCanBeRemoved(ModerationQueue $queue): void
    {
        if (!$this->getManager($queue)::getInstance()->canRemoveContent($queue)) {
            throw new PermissionDeniedException();
        }
    }

    private function getManager(ModerationQueue $queue): AbstractModerationQueueManager
    {
        $definition = ObjectTypeCache::getInstance()->getDefinition(
            ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->definitionID
        );

        return ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.moderation.type',
            $definition->definitionName
        )->getProcessor();
    }

    private function deleteContent(ModerationQueue $queue, string $message): void
    {
        $this->getManager($queue)->removeContent(
            $queue,
            $message
        );

        $editor = new ModerationQueueEditor($queue);

        $definition = ObjectTypeCache::getInstance()->getDefinition(
            ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->definitionID
        );
        if ($definition->definitionName === 'com.woltlab.wcf.moderation.type.report') {
            $editor->markAsConfirmed();
        } else {
            $editor->markAsRejected();
        }
    }
}

/** @internal */
final class DeleteContentParameters
{
    public function __construct(
        public readonly ?string $reason = null
    ) {}
}

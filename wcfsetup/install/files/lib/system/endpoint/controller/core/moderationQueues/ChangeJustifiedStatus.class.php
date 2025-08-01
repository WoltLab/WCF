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

/**
 * Changes the justified status of the report with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/moderation-queues/{id:\d+}/change-justified-status')]
final class ChangeJustifiedStatus implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = Helper::fetchObjectFromRequestParameter($variables['id'], ModerationQueue::class);

        $this->assertJustifiedStatusCanBeChanged($queue);

        $parameters = Helper::mapApiParameters($request, ChangeJustifiedStatusParameters::class);

        $additionalData = $queue->additionalData;
        // @phpstan-ignore function.alreadyNarrowedType
        if (!\is_array($additionalData)) {
            $additionalData = [];
        }
        $additionalData['markAsJustified'] = $parameters->markAsJustified;
        $editor = new ModerationQueueEditor($queue);
        $editor->update([
            'additionalData' => \serialize($additionalData),
        ]);

        return new JsonResponse([]);
    }

    private function assertJustifiedStatusCanBeChanged(ModerationQueue $queue): void
    {
        if (!$queue->canEdit() || !$queue->canChangeJustifiedStatus()) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class ChangeJustifiedStatusParameters
{
    public function __construct(
        public readonly bool $markAsJustified,
    ) {}
}

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
use wcf\system\WCF;

/**
 * Closes the report with the given ID by marking it as done without further processing.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/moderation-queues/{id:\d+}/close')]
final class CloseReport implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = Helper::fetchObjectFromRequestParameter($variables['id'], ModerationQueue::class);

        $this->assertReportCanBeClosed($queue);

        $parameters = Helper::mapApiParameters($request, CloseReportParameters::class);

        $editor = new ModerationQueueEditor($queue);
        $editor->markAsRejected($parameters->markAsJustified);

        return new JsonResponse([]);
    }

    private function assertReportCanBeClosed(ModerationQueue $queue): void
    {
        WCF::getSession()->checkPermissions(['mod.general.canUseModeration']);

        if (!$queue->canEdit()) {
            throw new PermissionDeniedException();
        }

        $definition = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->getDefinition();
        if ($definition->definitionName !== 'com.woltlab.wcf.moderation.report') {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class CloseReportParameters
{
    public function __construct(
        public readonly bool $markAsJustified,
    ) {}
}

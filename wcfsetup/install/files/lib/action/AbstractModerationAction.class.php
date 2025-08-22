<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\Psr15DialogForm;

/**
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @phpstan-type PerformActionResult array{
 *  result: array{
 *      assignee: ?array{
 *          username: string,
 *          userID: int,
 *          link: string,
 *      },
 *      status: string,
 *  }
 * }
 */
abstract class AbstractModerationAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id?: positive-int,
                    ids?: positive-int[]
                }
                EOT
        );

        if (!isset($parameters['id']) && !isset($parameters['ids'])) {
            throw new IllegalLinkException();
        }

        $objectIDs = $parameters['ids'] ?? [$parameters['id']];
        $moderationList = new ModerationQueueList();
        $moderationList->setObjectIDs($objectIDs);
        $moderationList->readObjects();

        if ($moderationList->count() === 0) {
            throw new IllegalLinkException();
        }

        foreach ($moderationList as $queue) {
            $this->assertCanEditQueueEntry($queue);
        }

        $form = $this->getForm($moderationList->getObjects());

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $jsonResponse = [];
            foreach ($moderationList as $queue) {
                $jsonResponse = \array_merge($jsonResponse, $this->performAction($queue, $form));
            }

            return new JsonResponse($jsonResponse);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    protected function assertCanEditQueueEntry(ModerationQueue $queue): void
    {
        if (!$queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @param ModerationQueue[] $moderationQueues
     */
    abstract protected function getForm(array $moderationQueues): Psr15DialogForm;

    /**
     * @return PerformActionResult|array{}
     */
    abstract protected function performAction(ModerationQueue $queue, Psr15DialogForm $form): array;
}

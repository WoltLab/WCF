<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\page\PageCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the content header title of a moderation queue entry.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[GetRequest('/core/moderation-queues/{id:\d+}/content-header-title')]
final class GetModerationQueueHeaderTitle implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $queue = ViewableModerationQueue::getViewableModerationQueue((int)$variables['id']);
        if ($queue === null) {
            throw new IllegalLinkException();
        }

        $this->assertQueueCanBeRead($queue);

        $controller = ModerationQueueManager::getInstance()->getController($queue->objectTypeID);
        if ($controller === null) {
            throw new IllegalLinkException();
        }

        $page = PageCache::getInstance()->getPageByController($controller);
        if ($page === null) {
            throw new IllegalLinkException();
        }

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'moderationContentHeader', [
                'queue' => $queue,
                'title' => $page->getTitle(),
            ]),
        ]);
    }

    private function assertQueueCanBeRead(ViewableModerationQueue $queue): void
    {
        if (!$queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }
}

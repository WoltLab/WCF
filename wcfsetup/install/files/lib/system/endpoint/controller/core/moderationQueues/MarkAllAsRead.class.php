<?php

namespace wcf\system\endpoint\controller\core\moderationQueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\moderation\queue\MarkAllModerationQueueAsRead;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Marks all moderation queue entries as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[PostRequest('/core/moderation-queues/mark-all-as-read')]
final class MarkAllAsRead implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['mod.general.canUseModeration']);

        (new MarkAllModerationQueueAsRead())();

        return new JsonResponse([]);
    }
}

<?php

namespace wcf\system\endpoint\controller\core\polls;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\poll\DeletePolls;
use wcf\data\poll\Poll;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the standalone poll with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/polls/{id:\d+}')]
final class DeletePoll implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $poll = Helper::fetchObjectFromRequestParameter($variables['id'], Poll::class);

        if (!$poll->canManage()) {
            throw new PermissionDeniedException();
        }

        new DeletePolls([$poll])();

        return new JsonResponse([]);
    }
}

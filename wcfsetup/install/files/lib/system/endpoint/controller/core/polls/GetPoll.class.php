<?php

namespace wcf\system\endpoint\controller\core\polls;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\poll\Poll;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Returns the data of the standalone poll with the given ID that is required
 * to embed it.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/polls/{id:\d+}')]
final class GetPoll implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $poll = Helper::fetchObjectFromRequestParameter($variables['id'], Poll::class);

        if (!$poll->isStandalone() || !$poll->isAccessible()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'pollID' => $poll->pollID,
            'question' => $poll->getQuestion(),
            'votes' => $poll->votes,
            'time' => $poll->time,
            'endTime' => $poll->endTime,
            'isFinished' => $poll->isFinished(),
        ]);
    }
}

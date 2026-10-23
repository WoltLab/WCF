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
use wcf\system\poll\PollManager;
use wcf\system\WCF;

/**
 * Returns the rendered results of the poll with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/polls/{id:\d+}/results')]
final class GetResults implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $poll = Helper::fetchObjectFromRequestParameter($variables['id'], Poll::class);
        PollManager::getInstance()->loadRelatedObject($poll);

        if (!$poll->canSeeResult()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'pollResult', [
                'poll' => $poll,
            ]),
        ]);
    }
}

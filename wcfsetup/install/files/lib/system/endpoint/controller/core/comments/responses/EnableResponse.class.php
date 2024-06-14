<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * API endpoint for enabling responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments/responses/{id:\d+}/enable')]
final class EnableResponse implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $response = Helper::fetchObjectFromRequestParameter($variables['id'], CommentResponse::class);

        $this->assertResponseCanBeEnabled($response);

        (new \wcf\system\comment\response\command\PublishResponse($response))();

        return new JsonResponse([]);
    }

    private function assertResponseCanBeEnabled(CommentResponse $response): void
    {
        if (!$response->isDisabled) {
            throw new IllegalLinkException();
        }

        $comment = $response->getComment();
        $processor = CommentHandler::getInstance()->getObjectType($comment->objectTypeID)->getProcessor();
        \assert($processor instanceof ICommentManager);
        if (!$processor->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }
}

<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * API endpoint for the deletion of responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[DeleteRequest('/core/comments/responses/{id:\d+}')]
final class DeleteResponse implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $response = Helper::fetchObjectFromRequestParameter($variables['id'], CommentResponse::class);

        $this->assertResponseIsDeletable($response);

        (new \wcf\system\comment\response\command\DeleteResponses([$response]))();

        return new JsonResponse([]);
    }

    private function assertResponseIsDeletable(CommentResponse $response): void
    {
        $manager = CommentHandler::getInstance()->getCommentManagerByID($response->getComment()->objectTypeID);
        if (!$manager->canDeleteResponse($response)) {
            throw new PermissionDeniedException();
        }
    }
}

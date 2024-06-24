<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * API endpoint for the rendering of a single comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/responses/{id:\d+}/render')]
final class RenderResponse implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $response = Helper::fetchObjectFromRequestParameter($variables['id'], CommentResponse::class);
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    messageOnly?: bool,
                    objectTypeID?: positive-int,
                }
                EOT,
        );

        $this->assertResponseIsAccessible($response, $parameters['objectTypeID'] ?? null);
        $this->markNotificationsAsRead($response);

        return new JsonResponse([
            'template' => $this->renderResponse($response, $parameters['messageOnly'] ?? false),
        ]);
    }

    private function assertResponseIsAccessible(CommentResponse $response, ?int $objectTypeID = null): void
    {
        $comment = $response->getComment();
        $objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);
        if ($objectTypeID !== null) {
            if ($objectType->objectTypeID !== $objectTypeID) {
                throw new IllegalLinkException();
            }
        }

        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->isAccessible($comment->objectID)) {
            throw new PermissionDeniedException();
        }
        if ($response->isDisabled && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    private function markNotificationsAsRead(CommentResponse $response): void
    {
        $objectType = CommentHandler::getInstance()->getObjectType($response->getComment()->objectTypeID)->objectType;
        CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
            $objectType,
            [$response]
        );
    }

    private function renderResponse(CommentResponse $response, bool $messageOnly = false): string
    {
        if ($response->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment.response',
                [$response->getObjectID()]
            );
        }

        if ($messageOnly) {
            return $response->getFormattedMessage();
        }

        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($response->getComment()->objectTypeID);

        $structedResponse = new StructuredCommentResponse($response);
        $structedResponse->setIsDeletable($commentManager->canDeleteResponse($response));
        $structedResponse->setIsEditable($commentManager->canEditResponse($response));

        return WCF::getTPL()->fetch('commentResponseList', 'wcf', [
            'responseList' => [$structedResponse],
            'commentCanModerate' => $commentManager->canModerate(
                $response->getComment()->objectTypeID,
                $response->getComment()->objectID
            ),
            'commentManager' => $commentManager,
        ]);
    }
}

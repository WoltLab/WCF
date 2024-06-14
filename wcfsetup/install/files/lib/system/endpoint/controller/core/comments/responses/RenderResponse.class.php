<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\object\type\ObjectTypeCache;
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
                    messageOnly: null|bool,
                    objectTypeID: null|positive-int,
                }
                EOT,
        );

        $this->assertResponseIsAccessible($response, $parameters['objectTypeID']);
        $this->markNotificationsAsRead($response);

        return new JsonResponse([
            'template' => $this->renderResponse($response, $parameters['messageOnly'] ?? false),
        ]);
    }

    private function assertResponseIsAccessible(CommentResponse $response, ?int $objectTypeID = null): void
    {
        $comment = $response->getComment();
        $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
        if ($objectTypeID !== null) {
            if ($objectType->objectTypeID !== $objectTypeID) {
                throw new IllegalLinkException();
            }
        }
        $commentProcessor = $objectType->getProcessor();

        if (!$commentProcessor->isAccessible($comment->objectID)) {
            throw new PermissionDeniedException();
        }
        if ($response->commentID != $comment->commentID) {
            throw new PermissionDeniedException();
        }
        if ($response->isDisabled && !$commentProcessor->canModerate($comment->objectTypeID, $comment->objectID)) {
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

        $commentProcessor = ObjectTypeCache::getInstance()->getObjectType($response->getComment()->objectTypeID)->getProcessor();

        $structedResponse = new StructuredCommentResponse($response);
        $structedResponse->setIsDeletable($commentProcessor->canDeleteResponse($response));
        $structedResponse->setIsEditable($commentProcessor->canEditResponse($response));

        return WCF::getTPL()->fetch('commentResponseList', 'wcf', [
            'responseList' => [$structedResponse],
            'commentCanModerate' => $commentProcessor->canModerate(
                $response->getComment()->objectTypeID,
                $response->getComment()->objectID
            ),
            'commentManager' => $commentProcessor,
        ]);
    }
}

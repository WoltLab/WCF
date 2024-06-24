<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\comment\StructuredComment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * API endpoint for the rendering of a single comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/{id:\d+}/render')]
final class RenderComment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $comment = Helper::fetchObjectFromRequestParameter($variables['id'], Comment::class);

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    responseID?: positive-int,
                    messageOnly?: bool,
                    objectTypeID?: positive-int,
                }
                EOT,
        );

        $this->assertCommentIsAccessible($comment, $parameters['objectTypeID'] ?? null);
        $response = null;
        if (isset($parameters['responseID'])) {
            $response = Helper::fetchObjectFromRequestParameter($parameters['responseID'], CommentResponse::class);
            $this->assertResponseIsAccessible($comment, $response);
        }

        $this->markNotificationsAsRead($comment, $response);

        return new JsonResponse(
            $this->renderComment($comment, $response, $parameters['messageOnly'] ?? false),
        );
    }

    private function assertCommentIsAccessible(Comment $comment, ?int $objectTypeID = null): void
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
        if ($objectTypeID !== null) {
            if ($objectType->objectTypeID !== $objectTypeID) {
                throw new IllegalLinkException();
            }
        }

        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->isAccessible($comment->objectID)) {
            throw new PermissionDeniedException();
        }
        if ($comment->isDisabled && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    private function assertResponseIsAccessible(Comment $comment, CommentResponse $response): void
    {
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);

        if ($response->commentID != $comment->commentID) {
            throw new PermissionDeniedException();
        }
        if ($response->isDisabled && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    private function markNotificationsAsRead(Comment $comment, ?CommentResponse $response = null)
    {
        $objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID)->objectType;
        if ($response === null) {
            CommentHandler::getInstance()->markNotificationsAsConfirmedForComments(
                $objectType,
                [new StructuredComment($comment)]
            );
        } else {
            CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
                $objectType,
                [$response]
            );
        }
    }

    private function renderComment(Comment $comment, ?CommentResponse $response = null, bool $messageOnly = false): array
    {
        if ($comment->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment',
                [$comment->getObjectID()]
            );
        }

        if ($messageOnly) {
            $returnValue = [
                'template' => $comment->getFormattedMessage(),
            ];

            if ($response !== null) {
                $returnValue['response'] = $this->renderResponse($response, $messageOnly);
            }

            return $returnValue;
        }

        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);

        $structuredComment = new StructuredComment($comment);
        $structuredComment->setIsDeletable($commentManager->canDeleteComment($comment));
        $structuredComment->setIsEditable($commentManager->canEditComment($comment));

        if ($response !== null) {
            // check if response is not visible
            foreach ($comment as $visibleResponse) {
                \assert($visibleResponse instanceof CommentResponse);
                if ($visibleResponse->responseID == $response->responseID) {
                    $response = null;
                    break;
                }
            }
        }

        // This functions renders a single comment without rendering its responses.
        // We need to prevent the setting of the data attribute for the last response time
        // so that the loading of the responses by the user works correctly.
        if ($comment->responses) {
            WCF::getTPL()->assign('ignoreLastResponseTime', true);
        }

        WCF::getTPL()->assign([
            'commentCanAdd' => $commentManager->canAdd(
                $comment->objectID
            ),
            'commentCanModerate' => $commentManager->canModerate(
                $comment->objectTypeID,
                $comment->objectID
            ),
            'commentList' => [$structuredComment],
            'commentManager' => $commentManager,
        ]);

        // load like data
        if (MODULE_LIKE) {
            $likeData = [];
            $commentObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment');
            ReactionHandler::getInstance()->loadLikeObjects($commentObjectType, [$comment->commentID]);
            $likeData['comment'] = ReactionHandler::getInstance()->getLikeObjects($commentObjectType);

            $responseIDs = [];
            foreach ($structuredComment as $visibleResponse) {
                $responseIDs[] = $visibleResponse->responseID;
            }

            if ($response !== null) {
                $responseIDs[] = $response->responseID;
            }

            if (!empty($responseIDs)) {
                $responseObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
                ReactionHandler::getInstance()->loadLikeObjects($responseObjectType, $responseIDs);
                $likeData['response'] = ReactionHandler::getInstance()->getLikeObjects($responseObjectType);
            }

            WCF::getTPL()->assign('likeData', $likeData);
        }

        $returnValue = [
            'template' => WCF::getTPL()->fetch('commentList'),
        ];
        if ($response !== null) {
            $returnValue['response'] = $this->renderResponse($response);
        }

        return $returnValue;
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

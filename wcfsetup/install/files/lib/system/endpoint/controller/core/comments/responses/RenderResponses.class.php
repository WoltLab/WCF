<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\data\comment\response\StructuredCommentResponseList;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * API endpoint for loading additional rendered responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/responses/render')]
final class RenderResponses implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderReponsesParameters::class);
        $comment = Helper::fetchObjectFromRequestParameter($parameters->commentID, Comment::class);
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);

        if (!$commentManager->isAccessible($comment->objectID)) {
            throw new PermissionDeniedException();
        }

        $commentCanModerate = $commentManager->canModerate(
            $comment->objectTypeID,
            $comment->objectID
        );

        // get response list
        $responseList = new StructuredCommentResponseList($commentManager, $comment);
        if ($parameters->lastResponseID) {
            $responseList->getConditionBuilder()->add(
                "(comment_response.time > ? OR (comment_response.time = ? && comment_response.responseID > ?))",
                [
                    $parameters->lastResponseTime,
                    $parameters->lastResponseTime,
                    $parameters->lastResponseID,
                ]
            );
        } else {
            $responseList->getConditionBuilder()->add(
                "comment_response.time > ?",
                [$parameters->lastResponseTime]
            );
        }
        if (!$commentCanModerate) {
            $responseList->getConditionBuilder()->add("comment_response.isDisabled = ?", [0]);
        }
        $responseList->readObjects();

        $lastResponseTime = $lastResponseID = 0;
        foreach ($responseList as $response) {
            if (!$lastResponseTime) {
                $lastResponseTime = $response->time;
            }
            if (!$lastResponseID) {
                $lastResponseID = $response->responseID;
            }

            $lastResponseTime = \max($lastResponseTime, $response->time);
            $lastResponseID = \max($lastResponseID, $response->responseID);
        }

        CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
            CommentHandler::getInstance()->getObjectType($comment->objectTypeID)->objectType,
            $responseList->getObjects()
        );

        return new JsonResponse([
            'lastResponseTime' => $lastResponseTime,
            'lastResponseID' => $lastResponseID,
            'template' => WCF::getTPL()->fetch('commentResponseList', 'wcf', [
                'commentCanModerate' => $commentCanModerate,
                'likeData' => MODULE_LIKE ? $responseList->getLikeData() : [],
                'responseList' => $responseList,
                'commentManager' => $commentManager,
            ]),
        ]);
    }
}

/** @internal */
final class RenderReponsesParameters
{
    public function __construct(
        /** @var positive-int **/
        public readonly int $commentID,

        /** @var positive-int **/
        public readonly int $lastResponseTime,

        /** @var positive-int **/
        public readonly int $lastResponseID,

        public readonly bool $loadAllResponses,
    ) {
    }
}

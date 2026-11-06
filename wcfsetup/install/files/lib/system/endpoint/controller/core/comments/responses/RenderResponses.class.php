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
 * Retrieves the HTML code for the rendering of a list of comment responses.
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

        if ($comment->isDisabled && !$commentCanModerate) {
            throw new PermissionDeniedException();
        }

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

        // The list is ordered by `time, responseID`, the last response is the
        // cursor for the next request. Keep the current cursor if there are no
        // further responses to prevent the client from resetting it.
        $lastResponseTime = $parameters->lastResponseTime;
        $lastResponseID = $parameters->lastResponseID;
        $responses = $responseList->getObjects();
        if ($responses !== []) {
            $lastResponse = \end($responses);
            $lastResponseTime = $lastResponse->time;
            $lastResponseID = $lastResponse->responseID;
        }

        CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
            CommentHandler::getInstance()->getObjectType($comment->objectTypeID)->objectType,
            $responses
        );

        return new JsonResponse([
            'lastResponseTime' => $lastResponseTime,
            'lastResponseID' => $lastResponseID,
            'template' => WCF::getTPL()->render('wcf', 'commentResponseList', [
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

        /** @var non-negative-int **/
        public readonly int $lastResponseTime,

        public readonly int $lastResponseID,

        public readonly bool $loadAllResponses,
    ) {}
}

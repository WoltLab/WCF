<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\user\User;
use wcf\event\comment\response\ResponseCreated;
use wcf\system\event\EventHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Creates a new comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CreateResponse
{
    public function __construct(
        private readonly Comment $comment,
        private readonly HtmlInputProcessor $htmlInputProcessor,
        private readonly ?User $user = null,
        private readonly string $username = '',
        private readonly bool $isDisabled = false,
    ) {
    }

    public function __invoke(): CommentResponse
    {
        $action = new CommentResponseAction([], 'create', [
            'data' => [
                'commentID' => $this->comment->commentID,
                'time' => TIME_NOW,
                'userID' => $this->user ? $this->user->userID : null,
                'username' => $this->user ? $this->user->username : $this->username,
                'message' => $this->htmlInputProcessor->getHtml(),
                'enableHtml' => 1,
                'isDisabled' => $this->isDisabled ? 1 : 0,
            ]
        ]);
        /** @var CommentResponse $response */
        $response = $action->executeAction()['returnValues'];

        $this->updateResponseData($response);

        if (!$response->isDisabled) {
            (new PublishResponse($response))();
        } else {
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.response',
                $response->responseID
            );
        }

        $this->htmlInputProcessor->setObjectID($response->getObjectID());
        if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor)) {
            (new CommentResponseEditor($response))->update([
                'hasEmbeddedObjects' => 1,
            ]);
            $response = new CommentResponse($response->getObjectID());
        }

        $event = new ResponseCreated($response);
        EventHandler::getInstance()->fire($event);

        return $response;
    }

    private function updateResponseData(CommentResponse $response): void
    {
        $unfilteredResponseIDs = $this->comment->getUnfilteredResponseIDs();
        if (\count($unfilteredResponseIDs) < 5) {
            $unfilteredResponseIDs[] = $response->responseID;
        }
        $unfilteredResponses = $this->comment->unfilteredResponses + 1;

        (new CommentEditor($this->comment))->update([
            'unfilteredResponseIDs' => \serialize($unfilteredResponseIDs),
            'unfilteredResponses' => $unfilteredResponses,
        ]);
    }
}

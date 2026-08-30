<?php

namespace wcf\command\comment\response;

use wcf\data\comment\CommentBuilder;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseBuilder;
use wcf\event\comment\response\ResponseCreated;
use wcf\system\event\EventHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Creates a new comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CreateResponse
{
    public function __construct(
        private readonly CommentResponseBuilder $builder,
    ) {}

    public function __invoke(): CommentResponse
    {
        $response = $this->builder->create();

        $this->updateResponseData($response);

        if ($response->isDisabled === 0) {
            new PublishResponse($response)();
        } else {
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.response',
                $response->responseID
            );
        }

        EventHandler::getInstance()->fire(new ResponseCreated($response, $this->builder));

        return $response;
    }

    private function updateResponseData(CommentResponse $response): void
    {
        $comment = $this->builder->comment;

        $unfilteredResponseIDs = $comment->getUnfilteredResponseIDs();
        if (\count($unfilteredResponseIDs) < 5) {
            $unfilteredResponseIDs[] = $response->responseID;
        }

        CommentBuilder::forUpdate($comment)
            ->setUnfilteredResponseIDs($unfilteredResponseIDs)
            ->incrementUnfilteredResponses(1)
            ->update();
    }
}

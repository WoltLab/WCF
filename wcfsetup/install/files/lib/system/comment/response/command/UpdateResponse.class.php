<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\event\comment\response\ResponseUpdated;
use wcf\system\event\EventHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Updates a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UpdateResponse
{
    public function __construct(
        private readonly CommentResponse $response,
        private readonly HtmlInputProcessor $htmlInputProcessor,
    ) {
    }

    public function __invoke(): void
    {
        $data = [
            'message' => $this->htmlInputProcessor->getHtml(),
        ];

        $this->htmlInputProcessor->setObjectID($this->response->getObjectID());
        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor);
        if ($this->response->hasEmbeddedObjects != $hasEmbeddedObjects) {
            $data['hasEmbeddedObjects'] = $this->response->hasEmbeddedObjects ? 0 : 1;
        }

        $action = new CommentResponseAction([$this->response], 'update', [
            'data' => $data,
        ]);
        $action->executeAction();

        $event = new ResponseUpdated(new CommentResponse($this->response->commentID));
        EventHandler::getInstance()->fire($event);
    }
}

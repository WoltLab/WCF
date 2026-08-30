<?php

namespace wcf\data\comment\response;

use wcf\data\comment\Comment;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Builder for creating and updating comment responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<CommentResponse>
 */
final class CommentResponseBuilder extends DatabaseObjectBuilder
{
    public private(set) Comment $comment;

    public private(set) HtmlInputProcessor $htmlInputProcessor;

    public function setComment(Comment $comment): static
    {
        $this->comment = $comment;
        $this->properties['commentID'] = $comment->commentID;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the author of the response. Responses written by a guest have no
     * user, use `setGuest()` to store the name that was provided by the guest.
     */
    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;
        $this->properties['username'] = $user->username;

        return $this;
    }

    public function setGuest(string $username): static
    {
        $this->properties['userID'] = null;
        $this->properties['username'] = $username;

        return $this;
    }

    public function setMessage(string $message): static
    {
        $this->properties['message'] = $message;

        return $this;
    }

    /**
     * Sets the message using the given input processor and marks the message as
     * HTML. The embedded objects of the message are registered when the response
     * is persisted.
     */
    public function setHtmlInputProcessor(HtmlInputProcessor $htmlInputProcessor): static
    {
        $this->htmlInputProcessor = $htmlInputProcessor;
        $this->setMessage($htmlInputProcessor->getHtml());
        $this->setEnableHtml(true);

        return $this;
    }

    public function setEnableHtml(bool $enableHtml): static
    {
        $this->properties['enableHtml'] = $enableHtml ? 1 : 0;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    public function setHasEmbeddedObjects(bool $hasEmbeddedObjects): static
    {
        $this->properties['hasEmbeddedObjects'] = $hasEmbeddedObjects ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if (isset($this->htmlInputProcessor)) {
            $this->registerEmbeddedObjects($object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->htmlInputProcessor)) {
            $this->registerEmbeddedObjects($object);
        }
    }

    private function registerEmbeddedObjects(CommentResponse $response): void
    {
        $this->htmlInputProcessor->setObjectID($response->responseID);

        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()
            ->registerObjects($this->htmlInputProcessor);
        if ($hasEmbeddedObjects === ($response->hasEmbeddedObjects !== 0)) {
            return;
        }

        CommentResponseBuilder::forUpdate($response)
            ->setHasEmbeddedObjects($hasEmbeddedObjects)
            ->update();
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['commentID', 'time', 'username', 'message'];
    }
}

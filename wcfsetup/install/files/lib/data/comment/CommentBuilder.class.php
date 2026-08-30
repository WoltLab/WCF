<?php

namespace wcf\data\comment;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Builder for creating and updating comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Comment>
 */
final class CommentBuilder extends DatabaseObjectBuilder
{
    public private(set) HtmlInputProcessor $htmlInputProcessor;

    public function setObjectType(ObjectType $objectType): static
    {
        $this->properties['objectTypeID'] = $objectType->objectTypeID;

        return $this;
    }

    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the author of the comment. Comments written by a guest have no user,
     * use `setUsername()` to store the name that was provided by the guest.
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
     * HTML. The embedded objects of the message are registered when the comment
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

    public function setResponses(int $responses): static
    {
        $this->properties['responses'] = $responses;

        return $this;
    }

    public function incrementResponses(int $responses): static
    {
        $this->incrementProperties['responses'] = $responses;

        return $this;
    }

    public function setUnfilteredResponses(int $unfilteredResponses): static
    {
        $this->properties['unfilteredResponses'] = $unfilteredResponses;

        return $this;
    }

    public function incrementUnfilteredResponses(int $unfilteredResponses): static
    {
        $this->incrementProperties['unfilteredResponses'] = $unfilteredResponses;

        return $this;
    }

    /**
     * Sets the ids of the five latest responses that are visible to everybody.
     *
     * @param int[] $responseIDs
     */
    public function setResponseIDs(array $responseIDs): static
    {
        $this->properties['responseIDs'] = \serialize($responseIDs);

        return $this;
    }

    /**
     * Sets the ids of the five latest responses, including disabled ones.
     *
     * @param int[] $responseIDs
     */
    public function setUnfilteredResponseIDs(array $responseIDs): static
    {
        $this->properties['unfilteredResponseIDs'] = \serialize($responseIDs);

        return $this;
    }

    /**
     * Reads the ids of the five latest responses that are visible to everybody
     * from the database.
     */
    public function recalculateResponseIDs(): static
    {
        $sql = "SELECT      responseID
                FROM        wcf1_comment_response
                WHERE       commentID = ?
                        AND isDisabled = ?
                ORDER BY    time ASC, responseID ASC";
        $statement = WCF::getDB()->prepare($sql, 5);
        $statement->execute([$this->getObject()->commentID, 0]);

        return $this->setResponseIDs($statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * Reads the ids of the five latest responses from the database, including
     * disabled ones.
     */
    public function recalculateUnfilteredResponseIDs(): static
    {
        $sql = "SELECT      responseID
                FROM        wcf1_comment_response
                WHERE       commentID = ?
                ORDER BY    time ASC, responseID ASC";
        $statement = WCF::getDB()->prepare($sql, 5);
        $statement->execute([$this->getObject()->commentID]);

        return $this->setUnfilteredResponseIDs($statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * Reads the number of responses that are visible to everybody from the
     * database.
     */
    public function recalculateResponses(): static
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_comment_response
                WHERE   commentID = ?
                    AND isDisabled = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObject()->commentID, 0]);

        return $this->setResponses((int)$statement->fetchSingleColumn());
    }

    /**
     * Reads the number of responses from the database, including disabled ones.
     */
    public function recalculateUnfilteredResponses(): static
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_comment_response
                WHERE   commentID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->getObject()->commentID]);

        return $this->setUnfilteredResponses((int)$statement->fetchSingleColumn());
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

    private function registerEmbeddedObjects(Comment $comment): void
    {
        $this->htmlInputProcessor->setObjectID($comment->commentID);

        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()
            ->registerObjects($this->htmlInputProcessor);
        if ($hasEmbeddedObjects === ($comment->hasEmbeddedObjects !== 0)) {
            return;
        }

        CommentBuilder::forUpdate($comment)
            ->setHasEmbeddedObjects($hasEmbeddedObjects)
            ->update();
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID', 'time', 'username', 'message'];
    }
}

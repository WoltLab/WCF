<?php

namespace wcf\data\comment;

use wcf\data\CollectionDatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\data\user\UserProfile;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\reaction\ReactionData;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a comment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $commentID              unique id of the comment
 * @property-read   int     $objectTypeID           id of the `com.woltlab.wcf.comment.commentableContent` object type
 * @property-read   int     $objectID               id of the commented object of the object type represented by `$objectTypeID`
 * @property-read   int     $time                   timestamp at which the comment has been written
 * @property-read   ?int    $userID                 id of the user who wrote the comment or `null` if the user does not exist anymore or if the comment has been written by a guest
 * @property-read   string  $username               name of the user or guest who wrote the comment
 * @property-read   string  $message                comment message
 * @property-read   int     $responses              number of responses on the comment
 * @property-read   string  $responseIDs            serialized array with the ids of the five latest comment responses
 * @property-read   int     $unfilteredResponses    number of all responses on the comment, including disabled ones
 * @property-read   string  $unfilteredResponseIDs  serialized array with the ids of the five latest comment responses, including disabled ones
 * @property-read   0|1     $enableHtml             is `1` if HTML will rendered in the comment, otherwise `0`
 * @property-read   0|1     $isDisabled             is `1` if the comment is disabled, otherwise `0`
 * @property-read   0|1     $hasEmbeddedObjects     is `1` if there are embedded objects in the comment, otherwise `0`
 *
 * @extends CollectionDatabaseObject<CommentCollection>
 */
class Comment extends CollectionDatabaseObject implements IMessage
{
    use TUserContent;

    /**
     * Returns a list of response ids.
     *
     * @return  int[]
     */
    public function getResponseIDs()
    {
        if ($this->responseIDs === '') {
            return [];
        }

        $responseIDs = @\unserialize($this->responseIDs);
        if ($responseIDs === false) {
            return [];
        }

        return $responseIDs;
    }

    /**
     * Returns a list of unfiltered response ids, including those that are still disabled.
     *
     * @return  int[]
     */
    public function getUnfilteredResponseIDs()
    {
        if ($this->unfilteredResponseIDs === '') {
            return [];
        }

        $responseIDs = @\unserialize($this->unfilteredResponseIDs);
        if ($responseIDs === false) {
            return [];
        }

        return $responseIDs;
    }

    #[\Override]
    public function getFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->process($this->message, 'com.woltlab.wcf.comment', $this->commentID);

        return $processor->getHtml();
    }

    /**
     * Returns a simplified version of the formatted message.
     *
     * @return  string
     */
    public function getSimplifiedFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($this->message, 'com.woltlab.wcf.comment', $this->commentID);

        return $processor->getHtml();
    }

    /**
     * @since 6.1
     */
    public function getPlainTextMessage(): string
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/plain');
        $processor->process($this->message, 'com.woltlab.wcf.comment', $this->commentID);

        return $processor->getHtml();
    }

    /**
     * Returns a version of this message optimized for use in emails.
     *
     * @param string $mimeType Either 'text/plain' or 'text/html'
     * @return  string
     */
    public function getMailText(string $mimeType = 'text/plain')
    {
        switch ($mimeType) {
            case 'text/plain':
                return $this->getPlainTextMessage();
            case 'text/html':
                return $this->getSimplifiedFormattedMessage();
        }

        throw new \LogicException('Unreachable');
    }

    /**
     * Returns the user profile of the comment author.
     *
     * @since 6.3
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    /**
     * Loads the embedded objects of all comments of the collection.
     *
     * @since 6.3
     */
    public function loadEmbeddedObjects(): void
    {
        $this->getCollection()->loadEmbeddedObjects('com.woltlab.wcf.comment');
    }

    #[\Override]
    public function getExcerpt(int $maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
    }

    #[\Override]
    public function getMessage()
    {
        return $this->message;
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->getCommentManager()->getCommentLink($this);
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getCommentManager()->getTitle(
            $this->objectTypeID,
            $this->objectID
        );
    }

    #[\Override]
    public function isVisible()
    {
        return true;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getFormattedMessage();
    }

    /**
     * @since 6.3
     */
    public function canViewReactions(): bool
    {
        return \MODULE_LIKE !== 0
            && WCF::getSession()->hasPermission('user.like.canViewLike')
            && $this->getCommentManager()->supportsLike();
    }

    /**
     * @since 6.3
     */
    public function canReact(): bool
    {
        return \MODULE_LIKE !== 0
            && !WCF::getUser()->isGuest()
            && $this->userID !== WCF::getUser()->userID
            && WCF::getSession()->hasPermission('user.like.canLike')
            && $this->getCommentManager()->supportsLike();
    }

    /**
     * @since 6.3
     */
    public function getReactionData(): ReactionData
    {
        return $this->getCollection()->getReactionData($this);
    }

    /**
     * @since 6.3
     */
    public function getCommentManager(): ICommentManager
    {
        return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor();
    }
}

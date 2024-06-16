<?php

namespace wcf\data\comment;

use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\util\StringUtil;

/**
 * Represents a comment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $commentID                  unique id of the comment
 * @property-read   int $objectTypeID               id of the `com.woltlab.wcf.comment.commentableContent` object type
 * @property-read   int $objectID                   id of the commented object of the object type represented by `$objectTypeID`
 * @property-read   int $time                       timestamp at which the comment has been written
 * @property-read   int|null $userID                id of the user who wrote the comment or `null` if the user does not exist anymore or if the comment has been written by a guest
 * @property-read   string $username                name of the user or guest who wrote the comment
 * @property-read   string $message                 comment message
 * @property-read   int $responses                  number of responses on the comment
 * @property-read   string $responseIDs             serialized array with the ids of the five latest comment responses
 * @property-read   int $unfilteredResponses        number of all responses on the comment, including disabled ones
 * @property-read   string $unfilteredResponseIDs   serialized array with the ids of the five latest comment responses, including disabled ones
 * @property-read   int $enableHtml                 is 1 if HTML will rendered in the comment, otherwise 0
 * @property-read   int $isDisabled                 is 1 if the comment is disabled, otherwise 0
 * @property-read   int $hasEmbeddedObjects         is `1` if there are embedded objects in the comment, otherwise `0`
 */
class Comment extends DatabaseObject implements IMessage
{
    use TUserContent;

    /**
     * Returns a list of response ids.
     *
     * @return  int[]
     */
    public function getResponseIDs()
    {
        if ($this->responseIDs === null || $this->responseIDs == '') {
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
        if ($this->unfilteredResponseIDs === null || $this->unfilteredResponseIDs == '') {
            return [];
        }

        $responseIDs = @\unserialize($this->unfilteredResponseIDs);
        if ($responseIDs === false) {
            return [];
        }

        return $responseIDs;
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
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
    public function getMailText($mimeType = 'text/plain')
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
     * @inheritDoc
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        /** @var ICommentManager $processor */
        $processor = CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor();

        return $processor->getCommentLink($this);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return CommentHandler::getInstance()->getObjectType($this->objectTypeID)->getProcessor()->getTitle(
            $this->objectTypeID,
            $this->objectID
        );
    }

    /**
     * @inheritDoc
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getFormattedMessage();
    }
}

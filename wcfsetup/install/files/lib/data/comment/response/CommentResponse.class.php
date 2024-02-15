<?php

namespace wcf\data\comment\response;

use wcf\data\comment\Comment;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\util\StringUtil;

/**
 * Represents a comment response.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $responseID unique id of the comment response
 * @property-read   int $commentID  id of the comment the comment response belongs to
 * @property-read   int $time       timestamp at which the comment response has been written
 * @property-read   int|null $userID     id of the user who wrote the comment response or `null` if the user does not exist anymore or if the comment response has been written by a guest
 * @property-read   string $username   name of the user or guest who wrote the comment response
 * @property-read   string $message    comment response message
 * @property-read       int $enableHtml     is 1 if HTML will rendered in the comment response, otherwise 0
 * @property-read   int $isDisabled is 1 if the comment response is disabled, otherwise 0
 * @property-read   int $hasEmbeddedObjects is `1` if there are embedded objects in the comment response, otherwise `0`
 */
class CommentResponse extends DatabaseObject implements IMessage
{
    use TUserContent;

    /**
     * comment object
     * @var Comment
     */
    protected $comment;

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        $processor = new HtmlOutputProcessor();
        $processor->process($this->message, 'com.woltlab.wcf.comment.response', $this->responseID);

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
        $processor->process($this->message, 'com.woltlab.wcf.comment.response', $this->responseID);

        return $processor->getHtml();
    }

    /**
     * @since 6.1
     */
    public function getPlainTextMessage(): string
    {
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/plain');
        $processor->process($this->message, 'com.woltlab.wcf.comment.response', $this->responseID);

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
     * Returns comment object related to this response.
     *
     * @return  Comment
     */
    public function getComment()
    {
        if ($this->comment === null) {
            $this->comment = new Comment($this->commentID);
        }

        return $this->comment;
    }

    /**
     * Sets related comment object.
     *
     * @param Comment $comment
     */
    public function setComment(Comment $comment)
    {
        if ($this->commentID == $comment->commentID) {
            $this->comment = $comment;
        }
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        /** @var ICommentManager $processor */
        $processor = CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor();

        return $processor->getResponseLink($this);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getTitle(
            $this->getComment()->objectTypeID,
            $this->getComment()->objectID,
            true
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

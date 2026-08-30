<?php

namespace wcf\data\comment\response;

use wcf\data\CollectionDatabaseObject;
use wcf\data\comment\Comment;
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
 * Represents a comment response.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $responseID         unique id of the comment response
 * @property-read   int     $commentID          id of the comment the comment response belongs to
 * @property-read   int     $time               timestamp at which the comment response has been written
 * @property-read   ?int    $userID             id of the user who wrote the comment response or `null` if the user does not exist anymore or if the comment response has been written by a guest
 * @property-read   string  $username           name of the user or guest who wrote the comment response
 * @property-read   string  $message            comment response message
 * @property-read   0|1     $enableHtml         is `1` if HTML will rendered in the comment response, otherwise `0`
 * @property-read   0|1     $isDisabled         is `1` if the comment response is disabled, otherwise `0`
 * @property-read   0|1     $hasEmbeddedObjects is `1` if there are embedded objects in the comment response, otherwise `0`
 *
 * @extends CollectionDatabaseObject<CommentResponseCollection>
 */
class CommentResponse extends CollectionDatabaseObject implements IMessage
{
    use TUserContent;

    #[\Override]
    public function getFormattedMessage()
    {
        $this->loadEmbeddedObjects();

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
        $this->loadEmbeddedObjects();

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
        $this->loadEmbeddedObjects();

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
     * Returns the user profile of the comment response author.
     *
     * @since 6.3
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    /**
     * Loads the embedded objects of all comment responses of the collection.
     *
     * @since 6.3
     */
    public function loadEmbeddedObjects(): void
    {
        $this->getCollection()->loadEmbeddedObjects('com.woltlab.wcf.comment.response');
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

    /**
     * Returns comment object related to this response.
     */
    public function getComment(): Comment
    {
        return $this->getCollection()->getComment($this);
    }

    /**
     * Sets related comment object.
     *
     * @return void
     * @deprecated 6.3 Does nothing.
     */
    public function setComment(Comment $comment) {}

    #[\Override]
    public function getLink(): string
    {
        /** @var ICommentManager $processor */
        $processor = CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor();

        return $processor->getResponseLink($this);
    }

    #[\Override]
    public function getTitle(): string
    {
        return CommentHandler::getInstance()->getObjectType($this->getComment()->objectTypeID)->getProcessor()->getTitle(
            $this->getComment()->objectTypeID,
            $this->getComment()->objectID,
            true
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
            && $this->getComment()->getCommentManager()->supportsLike();
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
            && $this->getComment()->getCommentManager()->supportsLike();
    }

    /**
     * @since 6.3
     */
    public function getReactionData(): ReactionData
    {
        return $this->getCollection()->getReactionData($this);
    }
}

<?php

namespace wcf\data\comment\response;

use wcf\data\comment\Comment;
use wcf\data\like\object\LikeObject;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\reaction\ReactionHandler;

/**
 * Provides a structured comment response list.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  StructuredCommentResponse       current()
 * @method  StructuredCommentResponse[]     getObjects()
 * @method  StructuredCommentResponse|null      getSingleObject()
 * @method  StructuredCommentResponse|null      search($objectID)
 * @property    StructuredCommentResponse[] $objects
 */
class StructuredCommentResponseList extends CommentResponseList
{
    /**
     * comment object
     * @var Comment
     */
    public $comment;

    /**
     * comment manager
     * @var ICommentManager
     */
    public $commentManager;

    /**
     * minimum response time
     * @var int
     */
    public $minResponseTime = 0;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = StructuredCommentResponse::class;

    /**
     * @inheritDoc
     */
    public $sqlLimit = 50;

    public function __construct(ICommentManager $commentManager, Comment $comment)
    {
        parent::__construct();

        $this->comment = $comment;
        $this->commentManager = $commentManager;

        $this->getConditionBuilder()->add("comment_response.commentID = ?", [$this->comment->commentID]);
    }

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        foreach ($this->objects as $response) {
            if ($this->minResponseTime === 0 || $response->time < $this->minResponseTime) {
                $this->minResponseTime = $response->time;
            }

            $response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
            $response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));
        }
    }

    /**
     * Fetches the like data.
     *
     * @return  LikeObject[][]
     * @deprecated 6.3 No longer in use.
     */
    public function getLikeData()
    {
        if ($this->objectIDs === null || $this->objectIDs === []) {
            return [];
        }

        $objectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
        ReactionHandler::getInstance()->loadLikeObjects($objectType, $this->objectIDs);

        return ['response' => ReactionHandler::getInstance()->getLikeObjects($objectType)];
    }

    /**
     * Returns minimum response time.
     *
     * @return  int
     */
    public function getMinResponseTime()
    {
        return $this->minResponseTime;
    }
}

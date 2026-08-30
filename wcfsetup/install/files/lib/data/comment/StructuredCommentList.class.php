<?php

namespace wcf\data\comment;

use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\like\object\LikeObject;
use wcf\system\cache\runtime\CommentResponseRuntimeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\reaction\ReactionHandler;

/**
 * Provides a structured comment list fetching last responses for every comment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends CommentList<StructuredComment>
 */
class StructuredCommentList extends CommentList
{
    /**
     * comment manager object
     * @var ICommentManager
     */
    public $commentManager;

    /**
     * minimum comment time
     * @var int
     */
    public $minCommentTime = 0;

    /**
     * object type id
     * @var int
     */
    public $objectTypeID = 0;

    /**
     * object id
     * @var int
     */
    public $objectID = 0;

    /**
     * ids of the responses of the comments in the list
     * @var int[]
     */
    public $responseIDs = [];

    /**
     * @inheritDoc
     */
    public $decoratorClassName = StructuredComment::class;

    /**
     * @inheritDoc
     */
    public $sqlLimit = 30;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'comment.time DESC';

    /**
     * enables/disables the loading of responses
     * @var bool
     */
    public $responseLoading = true;

    public function __construct(ICommentManager $commentManager, int $objectTypeID, int $objectID)
    {
        parent::__construct();

        $this->commentManager = $commentManager;
        $this->objectTypeID = $objectTypeID;
        $this->objectID = $objectID;

        $this->getConditionBuilder()->add("comment.objectTypeID = ?", [$objectTypeID]);
        $this->getConditionBuilder()->add("comment.objectID = ?", [$objectID]);
        $this->sqlLimit = $this->commentManager->getCommentsPerPage();

        if (!$this->commentManager->canModerate($objectTypeID, $objectID)) {
            $this->getConditionBuilder()->add('comment.isDisabled = 0');
        }
    }

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        $canModerate = $this->commentManager->canModerate($this->objectTypeID, $this->objectID);

        // fetch response ids
        $responseIDs = [];
        /** @var StructuredComment $comment */
        foreach ($this->objects as $comment) {
            if ($this->minCommentTime === 0 || $comment->time < $this->minCommentTime) {
                $this->minCommentTime = $comment->time;
            }

            if ($this->responseLoading) {
                $commentResponseIDs = ($canModerate) ? $comment->getUnfilteredResponseIDs() : $comment->getResponseIDs();
                foreach ($commentResponseIDs as $responseID) {
                    $this->responseIDs[] = $responseID;
                    $responseIDs[$responseID] = $comment->commentID;
                }
            }

            $comment->setIsDeletable($this->commentManager->canDeleteComment($comment->getDecoratedObject()));
            $comment->setIsEditable($this->commentManager->canEditComment($comment->getDecoratedObject()));

            CommentRuntimeCache::getInstance()->cacheComment($comment->getDecoratedObject());
        }

        // fetch last responses
        if ($responseIDs !== []) {
            $responseList = new CommentResponseList();
            $responseList->setObjectIDs(\array_keys($responseIDs));
            $responseList->readObjects();

            foreach ($responseList as $response) {
                $response = new StructuredCommentResponse($response);

                $response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
                $response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));

                $commentID = $responseIDs[$response->responseID];
                $this->objects[$commentID]->addResponse($response);

                CommentResponseRuntimeCache::getInstance()->cacheResponse($response->getDecoratedObject());
            }
        }
    }

    /**
     * Fetches the like data.
     *
     * @return array{comment: LikeObject[], response?: LikeObject[]}|array{}
     * @deprecated 6.3 No longer in use.
     */
    public function getLikeData()
    {
        if ($this->objectIDs === null || $this->objectIDs === []) {
            return [];
        }

        $likeData = [];
        $commentObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment');
        ReactionHandler::getInstance()->loadLikeObjects($commentObjectType, $this->getObjectIDs());
        $likeData['comment'] = ReactionHandler::getInstance()->getLikeObjects($commentObjectType);

        if ($this->responseIDs !== []) {
            $responseObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
            ReactionHandler::getInstance()->loadLikeObjects($responseObjectType, $this->responseIDs);
            $likeData['response'] = ReactionHandler::getInstance()->getLikeObjects($responseObjectType);
        }

        return $likeData;
    }

    /**
     * Returns minimum comment time.
     *
     * @return  int
     */
    public function getMinCommentTime()
    {
        return $this->minCommentTime;
    }

    /**
     * Returns the comment manager object.
     *
     * @return  ICommentManager
     */
    public function getCommentManager()
    {
        return $this->commentManager;
    }
}

<?php

namespace wcf\data\comment\response;

use wcf\data\comment\Comment;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for likeable comment responses.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LikeableCommentResponse     getObjectByID($objectID)
 * @method  LikeableCommentResponse[]   getObjectsByIDs(array $objectIDs)
 */
class LikeableCommentResponseProvider extends AbstractObjectTypeProvider implements
    ILikeObjectTypeProvider,
    IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $className = CommentResponse::class;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableCommentResponse::class;

    /**
     * @inheritDoc
     */
    public $listClassName = CommentResponseList::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        /** @var CommentResponse $object */

        if (!$object->responseID) {
            return false;
        }
        $comment = new Comment($object->commentID);
        if (!$comment->commentID) {
            return false;
        }

        $objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);

        return CommentHandler::getInstance()->getCommentManager($objectType->objectType)->isAccessible($comment->objectID);
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        $responseIDs = [];
        foreach ($likes as $like) {
            $responseIDs[] = $like->objectID;
        }

        // get objects type ids
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('comment_response.responseID IN (?)', [$responseIDs]);
        $sql = "SELECT      comment.objectTypeID, comment_response.responseID
                FROM        wcf1_comment_response comment_response
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = comment_response.commentID
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $responses = $statement->fetchMap('responseID', 'objectTypeID');

        // group likes by object type id
        $likeData = [];
        foreach ($likes as $like) {
            if (isset($responses[$like->objectID])) {
                if (!isset($likeData[$responses[$like->objectID]])) {
                    $likeData[$responses[$like->objectID]] = [];
                }
                $likeData[$responses[$like->objectID]][] = $like;
            }
        }

        foreach ($likeData as $objectTypeID => $likes) {
            $objectType = CommentHandler::getInstance()->getObjectType($objectTypeID);
            if (CommentHandler::getInstance()->getCommentManager($objectType->objectType) instanceof IViewableLikeProvider) {
                /** @noinspection PhpUndefinedMethodInspection */
                CommentHandler::getInstance()->getCommentManager($objectType->objectType)->prepare($likes);
            }
        }
    }
}

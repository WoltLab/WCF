<?php

namespace wcf\data\comment;

use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\like\IViewableLikeProvider;

/**
 * Object type provider for comments
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LikeableComment     getObjectByID($objectID)
 * @method  LikeableComment[]   getObjectsByIDs(array $objectIDs)
 */
class LikeableCommentProvider extends AbstractObjectTypeProvider implements
    ILikeObjectTypeProvider,
    IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $className = Comment::class;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableComment::class;

    /**
     * @inheritDoc
     */
    public $listClassName = CommentList::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        /** @var Comment $object */

        if (!$object->commentID) {
            return false;
        }

        $objectType = CommentHandler::getInstance()->getObjectType($object->objectTypeID);

        return CommentHandler::getInstance()->getCommentManager($objectType->objectType)->isAccessible($object->objectID);
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        $commentIDs = [];
        foreach ($likes as $like) {
            $commentIDs[] = $like->objectID;
        }

        // fetch comments
        $comments = ViewableCommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // group likes by object type id
        $likeData = [];
        foreach ($likes as $like) {
            if (isset($comments[$like->objectID])) {
                if (!isset($likeData[$comments[$like->objectID]->objectTypeID])) {
                    $likeData[$comments[$like->objectID]->objectTypeID] = [];
                }
                $likeData[$comments[$like->objectID]->objectTypeID][] = $like;
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

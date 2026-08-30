<?php

namespace wcf\data\comment;

use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable comment.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `Comment` instead.
 *
 * @mixin   Comment
 * @extends DatabaseObjectDecorator<Comment>
 */
class ViewableComment extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Comment::class;

    /**
     * Returns a specific comment decorated as comment entry.
     *
     * @return  ViewableComment
     */
    public static function getComment(int $commentID)
    {
        $list = new ViewableCommentList();
        $list->setObjectIDs([$commentID]);
        $list->readObjects();

        return $list->getSingleObject();
    }
}

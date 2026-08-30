<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable comment response.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `CommentResponse` instead.
 *
 * @mixin   CommentResponse
 * @extends DatabaseObjectDecorator<CommentResponse>
 */
class ViewableCommentResponse extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CommentResponse::class;

    /**
     * Returns a specific comment response decorated as viewable comment response.
     *
     * @return  ViewableCommentResponse
     */
    public static function getResponse(int $responseID)
    {
        $list = new ViewableCommentResponseList();
        $list->setObjectIDs([$responseID]);
        $list->readObjects();

        return $list->getSingleObject();
    }
}

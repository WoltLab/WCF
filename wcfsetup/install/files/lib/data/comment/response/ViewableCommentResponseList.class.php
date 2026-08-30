<?php

namespace wcf\data\comment\response;

/**
 * Represents a list of decorated comment response objects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `CommentResponseList` instead.
 *
 * @method  ViewableCommentResponse     current()
 * @method  ViewableCommentResponse[]   getObjects()
 * @method  ViewableCommentResponse|null    getSingleObject()
 * @method  ViewableCommentResponse|null    search($objectID)
 * @property    ViewableCommentResponse[] $objects
 */
class ViewableCommentResponseList extends CommentResponseList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableCommentResponse::class;
}

<?php

namespace wcf\data\comment;

/**
 * Represents a list of decorated comment objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `CommentList` instead.
 *
 * @method  ViewableComment     current()
 * @method  ViewableComment[]   getObjects()
 * @method  ViewableComment|null    getSingleObject()
 * @method  ViewableComment|null    search($objectID)
 * @property    ViewableComment[] $objects
 */
class ViewableCommentList extends CommentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableComment::class;
}

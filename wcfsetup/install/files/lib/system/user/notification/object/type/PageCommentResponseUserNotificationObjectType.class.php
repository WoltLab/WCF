<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * Represents a comment notification object type for comment responses on pages.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class PageCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType implements
    IMultiRecipientCommentUserNotificationObjectType
{
    use TMultiRecipientPageCommentUserNotificationObjectType;

    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = CommentResponseUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = CommentResponse::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = CommentResponseList::class;
}

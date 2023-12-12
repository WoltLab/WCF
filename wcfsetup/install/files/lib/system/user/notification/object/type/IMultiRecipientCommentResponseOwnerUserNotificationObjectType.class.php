<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\comment\Comment;

/**
 * @deprecated 6.1 The commentResponseOwner event is consistently fired, no matter if the event is a multi-recipient event or not.
 */
interface IMultiRecipientCommentResponseOwnerUserNotificationObjectType
{
    /**
     * Returns the user id of the comment owner.
     *
     * @param Comment $comment
     * @return  int
     */
    public function getCommentOwnerID(Comment $comment);
}

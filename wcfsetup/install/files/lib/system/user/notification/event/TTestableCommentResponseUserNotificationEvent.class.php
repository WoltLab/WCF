<?php

namespace wcf\system\user\notification\event;

use wcf\data\comment\CommentBuilder;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Default implementation of some methods of the testable user notification event interface
 * for comment response user notificiation events.
 *
 * As PHP 5.5 does not support abstract static functions in traits, we require them by this documentation:
 * - protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
 *  returns the `objectID` and `objectTypeID` parameter for comment creation.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TTestableCommentResponseUserNotificationEvent
{
    use TTestableUserNotificationEvent;

    #[\Override]
    public static function canBeTriggeredByGuests()
    {
        return true;
    }

    /**
     * Creates a test comment response.
     *
     * @return  CommentResponse
     */
    public static function createTestCommentResponse(UserProfile $recipient, UserProfile $author)
    {
        $objectData = self::getTestCommentObjectData($recipient, $author);

        $comment = CommentBuilder::forCreate()
            ->setObjectType(ObjectTypeCache::getInstance()->getObjectType($objectData['objectTypeID']))
            ->setObjectID($objectData['objectID'])
            ->setTime(\TIME_NOW - 10)
            ->setUser($recipient->getDecoratedObject())
            ->setMessage('<p>Test Comment</p>')
            ->setEnableHtml(true)
            ->setIsDisabled(false)
            ->create();

        /** @var ICommentManager $commentManager */
        $commentManager = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID)->getProcessor();
        $commentManager->updateCounter($comment->objectID, 1);

        /** @var CommentResponse $commentResponse */
        $commentResponse = (new CommentResponseAction([], 'create', [
            'data' => [
                'commentID' => $comment->commentID,
                'time' => \TIME_NOW - 10,
                'userID' => $author->userID,
                'username' => $author->username,
                'message' => 'Test Response',
                'isDisabled' => 0,
            ],
        ]))->executeAction()['returnValues'];

        CommentBuilder::forUpdate($comment)
            ->recalculateResponseIDs()
            ->recalculateUnfilteredResponseIDs()
            ->update();

        return $commentResponse;
    }

    #[\Override]
    public static function getTestAdditionalData(IUserNotificationObject $object)
    {
        /** @var CommentResponseUserNotificationObject $object */

        return [
            'commentID' => $object->commentID,
            'objectID' => $object->getComment()->objectID,
            'objectUserID' => $object->getComment()->objectID,
            'userID' => $object->getComment()->userID,
        ];
    }

    /**
     * @return  CommentResponseUserNotificationObject[]
     */
    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        return [new CommentResponseUserNotificationObject(self::createTestCommentResponse($recipient, $author))];
    }
}

<?php

namespace wcf\system\comment;

use wcf\command\comment\DeleteComments;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\comment\StructuredComment;
use wcf\data\comment\StructuredCommentList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\UserNotificationList;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\flood\FloodControl;
use wcf\system\message\censorship\Censorship;
use wcf\system\SingletonFactory;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Provides methods for comment object handling.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentHandler extends SingletonFactory
{
    /**
     * Limits the comments that `deleteObjects()` loads at once, together with their responses.
     */
    private const DELETE_OBJECTS_BATCH_SIZE = 500;

    /**
     * @var array{
     *  objectTypes: array<int, ObjectType>,
     *  objectTypeIDs: array<string, int>,
     * }|null
     */
    protected $cache;

    #[\Override]
    protected function init()
    {
        $this->cache = [
            'objectTypes' => [],
            'objectTypeIDs' => [],
        ];

        $cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.comment.commentableContent');
        foreach ($cache as $objectType) {
            $this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
            $this->cache['objectTypeIDs'][$objectType->objectType] = $objectType->objectTypeID;
        }
    }

    /**
     * Returns the id of the comment object type with the given name or `null` if no
     * such object type exists.
     *
     * @return ?int
     */
    public function getObjectTypeID(string $objectType)
    {
        return $this->cache['objectTypeIDs'][$objectType] ?? null;
    }

    /**
     * Returns the comment object type with the given name or `null` if no such
     * object type exists.
     *
     * @return ?ObjectType
     */
    public function getObjectType(int $objectTypeID)
    {
        return $this->cache['objectTypes'][$objectTypeID] ?? null;
    }

    /**
     * Returns comment manager object for given object type.
     *
     * @return ICommentManager
     * @throws SystemException
     */
    public function getCommentManager(string $objectType)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unable to find object type for '" . $objectType . "'");
        }

        return $this->getObjectType($objectTypeID)->getProcessor();
    }

    /**
     * Returns comment manager object for given object type id.
     *
     * @throws  \InvalidArgumentException
     * @since   6.1
     */
    public function getCommentManagerByID(int $objectTypeID): ICommentManager
    {
        $objectType = $this->getObjectType($objectTypeID);
        if ($objectType === null) {
            throw new \InvalidArgumentException('unknown object type id given');
        }

        $processor = $objectType->getProcessor();
        assert($processor instanceof ICommentManager);

        return $processor;
    }

    /**
     * Returns a comment list for a given object type and object id.
     *
     * @return StructuredCommentList
     */
    public function getCommentList(ICommentManager $commentManager, int $objectTypeID, int $objectID, bool $readObjects = true)
    {
        $commentList = new StructuredCommentList($commentManager, $objectTypeID, $objectID);
        if ($readObjects) {
            $commentList->readObjects();
        }

        return $commentList;
    }

    /**
     * Removes all comments for given objects.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function deleteObjects(string $objectType, array $objectIDs)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $lastCommentID = 0;
        do {
            $commentList = new CommentList();
            $commentList->getConditionBuilder()->add('comment.objectTypeID = ?', [$objectTypeID]);
            $commentList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
            $commentList->getConditionBuilder()->add('comment.commentID > ?', [$lastCommentID]);
            $commentList->sqlOrderBy = 'comment.commentID ASC';
            $commentList->sqlLimit = self::DELETE_OBJECTS_BATCH_SIZE;
            $commentList->readObjects();
            $comments = $commentList->getObjects();

            if ($comments === []) {
                return;
            }

            // The parent objects are being deleted and may already be gone, their counters must not be touched.
            new DeleteComments($comments, false)();

            $lastCommentID = \array_key_last($comments);
        } while (\count($comments) === self::DELETE_OBJECTS_BATCH_SIZE);
    }

    /**
     * Enforces the flood control.
     *
     * @return void
     * @throws NamedUserException if flood control is exceeded
     */
    public static function enforceFloodControl()
    {
        $floodControlTime = WCF::getSession()->getPermission('user.comment.floodControlTime');
        if (empty($floodControlTime)) {
            return;
        }

        $lastTime = FloodControl::getInstance()->getLastTime('com.woltlab.wcf.comment');
        if ($lastTime !== null && $lastTime > \TIME_NOW - $floodControlTime) {
            throw new NamedUserException(HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable(
                'wcf.comment.error.floodControl',
                ['lastCommentTime' => $lastTime]
            )));
        }
    }

    /**
     * Marks all comment-related notifications for objects of the given object type and with
     * the given ids as confirmed for the active user.
     *
     * @param string $objectType comment object type name
     * @param int[] $objectIDs ids of the objects whose comment-related notifications will be marked as confirmed
     * @param int $time only notifications older than the given timestamp will be marked as confirmed
     * @return void
     * @throws \InvalidArgumentException if invalid comment object type name is given
     * @since 5.2
     */
    public function markNotificationsAsConfirmed(string $objectType, array $objectIDs, int $time = \TIME_NOW)
    {
        // notifications are only relevant for logged-in users
        if (WCF::getUser()->isGuest()) {
            return;
        }

        if ($this->getObjectTypeID($objectType) === null) {
            throw new \InvalidArgumentException("Unknown comment object type '{$objectType}'.");
        }

        if ($objectIDs === []) {
            return;
        }

        // 1. comments

        // mark comment notifications as confirmed
        $commentEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.notification') as $event) {
                $commentEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.notification',
                ];
            }
        }

        if ($commentEvents !== []) {
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($commentEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->sqlJoins .= "
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = user_notification.objectID
                        AND comment.objectTypeID = " . \intval($this->getObjectTypeID($objectType));
            $notificationList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
            $notificationList->getConditionBuilder()->add('comment.time <= ?', [$time]);
            $notificationList->readObjects();

            $notificationObjectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($notificationObjectIDs[$notification->eventID])) {
                    $notificationObjectIDs[$notification->eventID] = [];
                }

                $notificationObjectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($notificationObjectIDs !== []) {
                foreach ($notificationObjectIDs as $eventID => $commentIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $commentEvents[$eventID]['eventName'],
                        $commentEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $commentIDs
                    );
                }
            }
        }

        // mark comment reaction notifications as confirmed
        $reactionCommentEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.like.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.like.notification') as $event) {
                $reactionCommentEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.like.notification',
                ];
            }
        }

        if ($reactionCommentEvents !== []) {
            // the value of the `objectID` property of the notifications is the like object
            // id which is currently unknown, thus it needs to be read from database
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($reactionCommentEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->sqlJoins .= "
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = user_notification.baseObjectID
                        AND comment.objectTypeID = " . \intval($this->getObjectTypeID($objectType));
            $notificationList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
            $notificationList->getConditionBuilder()->add('comment.time <= ?', [$time]);
            $notificationList->readObjects();

            $notificationObjectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($notificationObjectIDs[$notification->eventID])) {
                    $notificationObjectIDs[$notification->eventID] = [];
                }

                $notificationObjectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($notificationObjectIDs !== []) {
                foreach ($notificationObjectIDs as $eventID => $reactionIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $reactionCommentEvents[$eventID]['eventName'],
                        $reactionCommentEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $reactionIDs
                    );
                }
            }
        }

        // 2. responses

        // mark response notifications as confirmed
        $responseEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.notification') as $event) {
                $responseEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.response.notification',
                ];
            }
        }

        if ($responseEvents !== []) {
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($responseEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->sqlJoins .= "
                LEFT JOIN   wcf1_comment_response comment_response
                ON          comment_response.responseID = user_notification.objectID
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = comment_response.commentID";
            $notificationList->getConditionBuilder()->add(
                'comment.objectTypeID IN (?)',
                [$this->getObjectTypeID($objectType)]
            );
            $notificationList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
            $notificationList->getConditionBuilder()->add('comment_response.time <= ?', [$time]);
            $notificationList->readObjects();

            $notificationObjectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($notificationObjectIDs[$notification->eventID])) {
                    $notificationObjectIDs[$notification->eventID] = [];
                }

                $notificationObjectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($notificationObjectIDs !== []) {
                foreach ($notificationObjectIDs as $eventID => $responseIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $responseEvents[$eventID]['eventName'],
                        $responseEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $responseIDs
                    );
                }
            }
        }

        // mark comment response reaction notifications as confirmed
        $reactionResponseEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.like.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.like.notification') as $event) {
                $reactionResponseEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.response.like.notification',
                ];
            }
        }

        if ($reactionResponseEvents !== []) {
            // the value of the `objectID` property of the notifications is the like object
            // id which is currently unknown, thus it needs to be read from database
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($reactionResponseEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->sqlJoins .= "
                LEFT JOIN   wcf1_comment_response comment_response
                ON          comment_response.responseID = user_notification.baseObjectID
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = comment_response.commentID";
            $notificationList->getConditionBuilder()->add(
                'comment.objectTypeID IN (?)',
                [$this->getObjectTypeID($objectType)]
            );
            $notificationList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
            $notificationList->getConditionBuilder()->add('comment_response.time <= ?', [$time]);
            $notificationList->readObjects();

            $notificationObjectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($notificationObjectIDs[$notification->eventID])) {
                    $notificationObjectIDs[$notification->eventID] = [];
                }

                $notificationObjectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($notificationObjectIDs !== []) {
                foreach ($notificationObjectIDs as $eventID => $reactionIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $reactionResponseEvents[$eventID]['eventName'],
                        $reactionResponseEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $reactionIDs
                    );
                }
            }
        }
    }

    /**
     * Marks all comment-related notifications for objects of the given object type in the
     * given comment list as confirmed for the active user.
     *
     * @param StructuredComment[] $comments comments whose notifications will be marked as read
     * @return void
     * @throws \InvalidArgumentException if invalid comment object type name is given
     * @since 5.2
     */
    public function markNotificationsAsConfirmedForComments(string $objectType, array $comments)
    {
        // notifications are only relevant for logged-in users
        if (WCF::getUser()->isGuest()) {
            return;
        }

        if ($this->getObjectTypeID($objectType) === null) {
            throw new \InvalidArgumentException("Unknown comment object type '{$objectType}'.");
        }

        if (\count($comments) === 0) {
            return;
        }

        $commentIDs = [];
        foreach ($comments as $comment) {
            $commentIDs[] = $comment->commentID;
        }

        // 1. comments

        // mark comment notifications as confirmed
        $commentEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.notification') as $event) {
                $commentEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.notification',
                ];
            }
        }

        if ($commentEvents !== []) {
            foreach ($commentEvents as $eventData) {
                UserNotificationHandler::getInstance()->markAsConfirmed(
                    $eventData['eventName'],
                    $eventData['objectType'],
                    [WCF::getUser()->userID],
                    $commentIDs
                );
            }
        }

        // mark comment reaction notifications as confirmed
        $reactionCommentEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.like.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.like.notification') as $event) {
                $reactionCommentEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.like.notification',
                ];
            }
        }

        if ($reactionCommentEvents !== []) {
            // the value of the `objectID` property of the notifications is the like object
            // id which is currently unknown, thus it needs to be read from database
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($reactionCommentEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->getConditionBuilder()->add('user_notification.baseObjectID IN (?)', [$commentIDs]);
            $notificationList->readObjects();

            $objectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($objectIDs[$notification->eventID])) {
                    $objectIDs[$notification->eventID] = [];
                }

                $objectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($objectIDs !== []) {
                foreach ($objectIDs as $eventID => $reactionIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $reactionCommentEvents[$eventID]['eventName'],
                        $reactionCommentEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $reactionIDs
                    );
                }
            }
        }

        // 2. responses

        $responseIDs = [];
        foreach ($comments as $comment) {
            // as we do not know whether `Comment::getUnfilteredResponseIDs()`
            // or `Comment::getResponseIDs()` has been used, collect response
            // ids manually
            foreach ($comment as $response) {
                $responseIDs[] = $response->responseID;
            }
        }

        if ($responseIDs !== []) {
            // mark response notifications as confirmed
            $responseEvents = [];
            if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.notification') !== 0) {
                foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.notification') as $event) {
                    $responseEvents[$event->eventID] = [
                        'eventName' => $event->eventName,
                        'objectType' => $objectType . '.response.notification',
                    ];
                }
            }

            if ($responseEvents !== []) {
                foreach ($responseEvents as $eventData) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $eventData['eventName'],
                        $eventData['objectType'],
                        [WCF::getUser()->userID],
                        $responseIDs
                    );
                }
            }

            // mark comment response reaction notifications as confirmed
            $reactionResponseEvents = [];
            if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.like.notification') !== 0) {
                foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.like.notification') as $event) {
                    $reactionResponseEvents[$event->eventID] = [
                        'eventName' => $event->eventName,
                        'objectType' => $objectType . '.response.like.notification',
                    ];
                }
            }

            if ($reactionResponseEvents !== []) {
                // the value of the `objectID` property of the notifications is the like object
                // id which is currently unknown, thus it needs to be read from database
                $notificationList = new UserNotificationList();
                $notificationList->getConditionBuilder()->add(
                    'user_notification.eventID IN (?)',
                    [\array_keys($reactionResponseEvents)]
                );
                $notificationList->getConditionBuilder()->add(
                    'user_notification.userID = ?',
                    [WCF::getUser()->userID]
                );
                $notificationList->getConditionBuilder()->add('user_notification.baseObjectID IN (?)', [$responseIDs]);
                $notificationList->readObjects();

                $objectIDs = [];
                foreach ($notificationList as $notification) {
                    if (!isset($objectIDs[$notification->eventID])) {
                        $objectIDs[$notification->eventID] = [];
                    }

                    $objectIDs[$notification->eventID][] = $notification->objectID;
                }

                if ($objectIDs !== []) {
                    foreach ($objectIDs as $eventID => $reactionIDs) {
                        UserNotificationHandler::getInstance()->markAsConfirmed(
                            $reactionResponseEvents[$eventID]['eventName'],
                            $reactionResponseEvents[$eventID]['objectType'],
                            [WCF::getUser()->userID],
                            $reactionIDs
                        );
                    }
                }
            }
        }
    }

    /**
     * Marks all comment response-related notifications for objects of the given object type in
     * the given comment response list as confirmed for the active user.
     *
     * @param (CommentResponse|StructuredCommentResponse)[] $responses comment responses whose notifications will be marked as read
     * @return void
     *
     * @throws \InvalidArgumentException if invalid comment object type name is given
     * @since 5.2
     */
    public function markNotificationsAsConfirmedForResponses(string $objectType, array $responses)
    {
        // notifications are only relevant for logged-in users
        if (WCF::getUser()->isGuest()) {
            return;
        }

        if ($this->getObjectTypeID($objectType) === null) {
            throw new \InvalidArgumentException("Unknown comment object type '{$objectType}'.");
        }

        if (\count($responses) === 0) {
            return;
        }

        $responseIDs = [];
        foreach ($responses as $response) {
            $responseIDs[] = $response->responseID;
        }

        $responseEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.notification') as $event) {
                $responseEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.response.notification',
                ];
            }
        }

        if ($responseEvents !== []) {
            foreach ($responseEvents as $eventData) {
                UserNotificationHandler::getInstance()->markAsConfirmed(
                    $eventData['eventName'],
                    $eventData['objectType'],
                    [WCF::getUser()->userID],
                    $responseIDs
                );
            }
        }

        // mark comment response reaction notifications as confirmed
        $reactionResponseEvents = [];
        if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.response.like.notification') !== 0) {
            foreach (UserNotificationHandler::getInstance()->getEvents($objectType . '.response.like.notification') as $event) {
                $reactionResponseEvents[$event->eventID] = [
                    'eventName' => $event->eventName,
                    'objectType' => $objectType . '.response.like.notification',
                ];
            }
        }

        if ($reactionResponseEvents !== []) {
            // the value of the `objectID` property of the notifications is the like object
            // id which is currently unknown, thus it needs to be read from database
            $notificationList = new UserNotificationList();
            $notificationList->getConditionBuilder()->add(
                'user_notification.eventID IN (?)',
                [\array_keys($reactionResponseEvents)]
            );
            $notificationList->getConditionBuilder()->add('user_notification.userID = ?', [WCF::getUser()->userID]);
            $notificationList->getConditionBuilder()->add('user_notification.baseObjectID IN (?)', [$responseIDs]);
            $notificationList->readObjects();

            $objectIDs = [];
            foreach ($notificationList as $notification) {
                if (!isset($objectIDs[$notification->eventID])) {
                    $objectIDs[$notification->eventID] = [];
                }

                $objectIDs[$notification->eventID][] = $notification->objectID;
            }

            if ($objectIDs !== []) {
                foreach ($objectIDs as $eventID => $reactionIDs) {
                    UserNotificationHandler::getInstance()->markAsConfirmed(
                        $reactionResponseEvents[$eventID]['eventName'],
                        $reactionResponseEvents[$eventID]['objectType'],
                        [WCF::getUser()->userID],
                        $reactionIDs
                    );
                }
            }
        }
    }

    /**
     * Enforces the censorship.
     *
     * @return void
     * @throws UserInputException
     */
    public static function enforceCensorship(string $text)
    {
        $censoredWords = Censorship::getInstance()->test($text);
        if ($censoredWords !== false) {
            throw new UserInputException(
                'text',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.censoredWordsFound',
                    ['censoredWords' => $censoredWords]
                )
            );
        }
    }
}

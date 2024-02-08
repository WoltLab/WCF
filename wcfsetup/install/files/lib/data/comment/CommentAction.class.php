<?php

namespace wcf\data\comment;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\IMessageInlineEditorAction;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\flood\FloodControl;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\user\notification\object\type\IMultiRecipientCommentUserNotificationObjectType;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Executes comment-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Comment         create()
 * @method  CommentEditor[]     getObjects()
 * @method  CommentEditor       getSingleObject()
 */
class CommentAction extends AbstractDatabaseObjectAction implements IMessageInlineEditorAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = [
        'addComment',
        'addResponse',
        'loadComment',
        'loadComments',
        'loadResponse',
    ];

    /**
     * captcha object type used for comments
     * @var ObjectType
     */
    public $captchaObjectType;

    /**
     * @inheritDoc
     */
    protected $className = CommentEditor::class;

    /**
     * comment object
     * @var Comment
     */
    protected $comment;

    /**
     * comment processor
     * @var ICommentManager
     */
    protected $commentProcessor;

    /**
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * response object
     * @var CommentResponse
     */
    protected $response;

    /**
     * comment object created by addComment()
     * @var Comment
     */
    public $createdComment;

    /**
     * response object created by addResponse()
     * @var CommentResponse
     */
    public $createdResponse;

    /**
     * errors occurring through the validation of addComment or addResponse
     * @var array
     */
    public $validationErrors = [];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        $this->readObjects();

        if ($this->getObjects() === []) {
            throw new UserInputException('objectIDs');
        }

        foreach ($this->getObjects() as $comment) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
            $processor = $objectType->getProcessor();
            if (!$processor->canDeleteComment($comment->getDecoratedObject())) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        // update counters
        /** @var ICommentManager[] $processors */
        $processors = [];
        $groupCommentIDs = $commentIDs = [];
        foreach ($this->getObjects() as $comment) {
            if (!isset($processors[$comment->objectTypeID])) {
                $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
                $processors[$comment->objectTypeID] = $objectType->getProcessor();

                $groupCommentIDs[$comment->objectTypeID] = [];
            }

            if (!$comment->isDisabled) {
                $processors[$comment->objectTypeID]->updateCounter($comment->objectID, -1 * ($comment->responses + 1));
            }

            $groupCommentIDs[$comment->objectTypeID][] = $comment->commentID;
            $commentIDs[] = $comment->commentID;
        }

        if (!empty($groupCommentIDs)) {
            $likeObjectIDs = [];
            $notificationObjectTypes = [];
            foreach ($groupCommentIDs as $objectTypeID => $objectIDs) {
                // remove activity events
                $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
                if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType . '.recentActivityEvent')) {
                    UserActivityEventHandler::getInstance()->removeEvents(
                        $objectType->objectType . '.recentActivityEvent',
                        $objectIDs
                    );
                }

                $likeObjectIDs = \array_merge($likeObjectIDs, $objectIDs);

                // delete notifications
                $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
                if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType . '.notification')) {
                    UserNotificationHandler::getInstance()->removeNotifications(
                        $objectType->objectType . '.notification',
                        $objectIDs
                    );
                }

                if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType . '.like.notification')) {
                    $notificationObjectTypes[] = $objectType->objectType . '.like.notification';
                }
            }

            // remove likes
            ReactionHandler::getInstance()->removeReactions(
                'com.woltlab.wcf.comment',
                $likeObjectIDs,
                $notificationObjectTypes
            );
        }

        if (!empty($commentIDs)) {
            // delete responses
            $commentResponseList = new CommentResponseList();
            $commentResponseList->getConditionBuilder()->add('comment_response.commentID IN (?)', [$commentIDs]);
            $commentResponseList->readObjectIDs();
            if (\count($commentResponseList->getObjectIDs())) {
                $action = new CommentResponseAction($commentResponseList->getObjectIDs(), 'delete', [
                    'ignoreCounters' => true,
                ]);
                $action->executeAction();
            }

            ModerationQueueManager::getInstance()->removeQueues(
                'com.woltlab.wcf.comment.comment',
                $commentIDs
            );

            MessageEmbeddedObjectManager::getInstance()->removeObjects(
                'com.woltlab.wcf.comment',
                $commentIDs
            );
        }

        return parent::delete();
    }

    /**
     * Validates parameters to load comments.
     *
     * @throws  PermissionDeniedException
     */
    public function validateLoadComments()
    {
        $this->readInteger('lastCommentTime', false, 'data');
        $this->readInteger('objectID', false, 'data');

        $objectType = $this->validateObjectType();
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->isAccessible($this->parameters['data']['objectID'])) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns parsed comments.
     *
     * @return  array
     */
    public function loadComments()
    {
        $commentList = CommentHandler::getInstance()->getCommentList(
            $this->commentProcessor,
            $this->parameters['data']['objectTypeID'],
            $this->parameters['data']['objectID'],
            false
        );
        $commentList->getConditionBuilder()->add("comment.time < ?", [$this->parameters['data']['lastCommentTime']]);
        $commentList->readObjects();

        // mark notifications for loaded comments as read
        CommentHandler::getInstance()->markNotificationsAsConfirmedForComments(
            CommentHandler::getInstance()->getObjectType($this->parameters['data']['objectTypeID'])->objectType,
            $commentList->getObjects()
        );

        WCF::getTPL()->assign([
            'commentList' => $commentList,
            'likeData' => MODULE_LIKE ? $commentList->getLikeData() : [],
        ]);

        return [
            'lastCommentTime' => $commentList->getMinCommentTime(),
            'template' => WCF::getTPL()->fetch('commentList'),
        ];
    }

    /**
     * Validates the `loadComment` action.
     *
     * @throws  PermissionDeniedException
     * @since   3.1
     */
    public function validateLoadComment()
    {
        $this->readInteger('responseID', true);
        $this->readInteger('objectTypeID', true);
        $this->comment = $this->getSingleObject()->getDecoratedObject();
        if (!empty($this->parameters['objectTypeID']) && $this->parameters['objectTypeID'] !== $this->comment->objectTypeID) {
            throw new UserInputException('objectTypeID');
        }

        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->isAccessible($this->comment->objectID)) {
            throw new PermissionDeniedException();
        }
        if ($this->comment->isDisabled && !$this->commentProcessor->canModerate($this->comment->objectTypeID, $this->comment->objectID)) {
            throw new PermissionDeniedException();
        }

        if (!empty($this->parameters['responseID'])) {
            $this->response = new CommentResponse($this->parameters['responseID']);
            if (!$this->response->responseID) {
                throw new UserInputException('responseID');
            }
            if ($this->response->commentID != $this->comment->commentID) {
                throw new PermissionDeniedException();
            }
            if ($this->response->isDisabled && !$this->commentProcessor->canModerate($this->comment->objectTypeID, $this->comment->objectID)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Returns a rendered comment.
     *
     * @return  string[]
     * @since   3.1
     */
    public function loadComment()
    {
        // mark notifications for loaded comment/response as read
        $objectType = CommentHandler::getInstance()->getObjectType($this->comment->objectTypeID)->objectType;
        if ($this->response === null) {
            CommentHandler::getInstance()->markNotificationsAsConfirmedForComments(
                $objectType,
                [new StructuredComment($this->comment)]
            );
        } else {
            CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
                $objectType,
                [$this->response]
            );
        }

        $returnValues = $this->renderComment($this->comment, $this->response);

        return (\is_array($returnValues)) ? $returnValues : ['template' => $returnValues];
    }

    /**
     * Validates the `loadResponse` action.
     *
     * @since   3.1
     */
    public function validateLoadResponse()
    {
        $this->readInteger('responseID');
        $this->readInteger('objectTypeID', true);
        $this->response = new CommentResponse($this->parameters['responseID']);
        if (!$this->response->responseID) {
            throw new UserInputException('responseID');
        }

        $this->comment = $this->response->getComment();
        if (!empty($this->parameters['objectTypeID']) && $this->parameters['objectTypeID'] !== $this->comment->objectTypeID) {
            throw new UserInputException('objectTypeID');
        }

        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->isAccessible($this->comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns a rendered response.
     *
     * @return  string[]
     * @since   3.1
     */
    public function loadResponse()
    {
        return [
            'template' => $this->renderResponse($this->response),
        ];
    }

    /**
     * Validates parameters to add a comment.
     *
     * @throws  PermissionDeniedException
     */
    public function validateAddComment()
    {
        try {
            CommentHandler::enforceFloodControl();
        } catch (NamedUserException $e) {
            throw new UserInputException('message', $e->getMessage());
        }

        $this->readInteger('objectID', false, 'data');
        $this->validateGetGuestDialog();

        $this->validateMessage();
        $objectType = $this->validateObjectType();

        // validate object id and permissions
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Adds a comment.
     *
     * @return  string[]
     */
    public function addComment()
    {
        $guestDialog = $this->getGuestDialog();
        if ($guestDialog) {
            return [
                'guestDialog' => $guestDialog,
            ];
        }

        /** @var HtmlInputProcessor $htmlInputProcessor */
        $htmlInputProcessor = $this->parameters['htmlInputProcessor'];

        // create comment
        $this->createdComment = CommentEditor::create([
            'objectTypeID' => $this->parameters['data']['objectTypeID'],
            'objectID' => $this->parameters['data']['objectID'],
            'time' => TIME_NOW,
            'userID' => WCF::getUser()->userID ?: null,
            'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->parameters['data']['username'],
            'message' => $htmlInputProcessor->getHtml(),
            'responses' => 0,
            'responseIDs' => \serialize([]),
            'enableHtml' => 1,
            'isDisabled' => $this->commentProcessor->canAddWithoutApproval($this->parameters['data']['objectID']) ? 0 : 1,
        ]);

        if (!$this->createdComment->isDisabled) {
            $action = new self([$this->createdComment], 'triggerPublication', [
                'commentProcessor' => $this->commentProcessor,
            ]);
            $action->executeAction();
        } else {
            // mark comment for moderated content
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.comment',
                $this->createdComment->commentID
            );
        }

        $htmlInputProcessor->setObjectID($this->createdComment->getObjectID());
        if (MessageEmbeddedObjectManager::getInstance()->registerObjects($htmlInputProcessor)) {
            (new CommentEditor($this->createdComment))->update([
                'hasEmbeddedObjects' => 1,
            ]);
            $this->createdComment = new Comment($this->createdComment->getObjectID());
        }

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.comment');

        if (!$this->createdComment->userID) {
            // save user name is session
            WCF::getSession()->register('username', $this->createdComment->username);

            // save last comment time for flood control
            WCF::getSession()->register('lastCommentTime', $this->createdComment->time);

            // reset captcha for future requests
            if ($this->captchaObjectType) {
                $this->captchaObjectType->getProcessor()->reset();
            }
        }

        return [
            'template' => $this->renderComment($this->createdComment),
        ];
    }

    public function triggerPublication()
    {
        if (!empty($this->parameters['commentProcessor'])) {
            $objectType = null;
            if (!empty($this->objects)) {
                /** @var Comment $comment */
                $comment = \reset($this->objects);
                $objectType = $this->validateObjectType($comment->objectTypeID);
            }

            $this->commentProcessor = $this->parameters['commentProcessor'];
        } else {
            $objectType = $this->validateObjectType($this->parameters['objectTypeID']);
            $this->commentProcessor = $objectType->getProcessor();
        }

        /** @var CommentEditor $comment */
        foreach ($this->objects as $comment) {
            // update counter
            $comment->update(['isDisabled' => 0]);
            $this->commentProcessor->updateCounter($comment->objectID, 1);

            // fire activity event
            if ($comment->userID && UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType . '.recentActivityEvent')) {
                UserActivityEventHandler::getInstance()->fireEvent(
                    $objectType->objectType . '.recentActivityEvent',
                    $comment->commentID,
                    null,
                    $comment->userID,
                    $comment->time
                );
            }

            // fire notification event
            if (UserNotificationHandler::getInstance()->getEvent($objectType->objectType . '.notification', 'comment')) {
                $notificationObject = new CommentUserNotificationObject($comment->getDecoratedObject());
                $notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType . '.notification');
                \assert($notificationObjectType instanceof ICommentUserNotificationObjectType);

                if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
                    $recipientIDs = $notificationObjectType->getRecipientIDs($comment->getDecoratedObject());
                } else {
                    $recipientIDs = [];
                }

                $recipientIDs[] = $notificationObjectType->getOwnerID($comment->commentID);

                // make sure that the comment's author gets no notification
                $recipientIDs = \array_diff($recipientIDs, [$comment->getUserID()]);

                UserNotificationHandler::getInstance()->fireEvent(
                    'comment',
                    $objectType->objectType . '.notification',
                    $notificationObject,
                    $recipientIDs
                );
            }
        }
    }

    /**
     * Validates parameters to add a response.
     *
     * @throws  PermissionDeniedException
     */
    public function validateAddResponse()
    {
        try {
            CommentHandler::enforceFloodControl();
        } catch (NamedUserException $e) {
            throw new UserInputException('message', $e->getMessage());
        }

        $comment = $this->getSingleObject();
        $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->canAdd($comment->objectID)) {
            throw new PermissionDeniedException();
        }

        if ($comment->isDisabled && !$this->commentProcessor->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }

        $this->validateGetGuestDialog();
        $this->validateMessage(true);
    }

    /**
     * Adds a response.
     *
     * @return  array
     */
    public function addResponse()
    {
        $guestDialog = $this->getGuestDialog();
        if ($guestDialog) {
            return [
                'guestDialog' => $guestDialog,
            ];
        }

        $comment = $this->getSingleObject();

        /** @var HtmlInputProcessor $htmlInputProcessor */
        $htmlInputProcessor = $this->parameters['htmlInputProcessor'];

        // create response
        $this->createdResponse = CommentResponseEditor::create([
            'commentID' => $comment->commentID,
            'time' => TIME_NOW,
            'userID' => WCF::getUser()->userID ?: null,
            'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->parameters['data']['username'],
            'message' => $htmlInputProcessor->getHtml(),
            'enableHtml' => 1,
            'isDisabled' => $this->commentProcessor->canAddWithoutApproval($comment->objectID) ? 0 : 1,
        ]);
        $this->createdResponse->setComment($comment->getDecoratedObject());

        $htmlInputProcessor->setObjectID($this->createdResponse->getObjectID());
        if (MessageEmbeddedObjectManager::getInstance()->registerObjects($htmlInputProcessor)) {
            (new CommentResponseEditor($this->createdResponse))->update([
                'hasEmbeddedObjects' => 1,
            ]);
            $this->createdResponse = new CommentResponse($this->createdResponse->getObjectID());
        }

        // update response data
        $unfilteredResponseIDs = $comment->getUnfilteredResponseIDs();
        if (\count($unfilteredResponseIDs) < 5) {
            $unfilteredResponseIDs[] = $this->createdResponse->responseID;
        }
        $unfilteredResponses = $comment->unfilteredResponses + 1;

        // update comment
        $comment->update([
            'unfilteredResponseIDs' => \serialize($unfilteredResponseIDs),
            'unfilteredResponses' => $unfilteredResponses,
        ]);

        if (!$this->createdResponse->isDisabled) {
            $action = new self([], 'triggerPublicationResponse', [
                'commentProcessor' => $this->commentProcessor,
                'responses' => [$this->createdResponse],
            ]);
            $action->executeAction();
        } else {
            // mark response for moderated content
            ModerationQueueActivationManager::getInstance()->addModeratedContent(
                'com.woltlab.wcf.comment.response',
                $this->createdResponse->responseID
            );
        }

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.comment');

        if (!$this->createdResponse->userID) {
            // save user name is session
            WCF::getSession()->register('username', $this->createdResponse->username);

            // save last comment time for flood control
            WCF::getSession()->register('lastCommentTime', $this->createdResponse->time);

            // reset captcha for future requests
            if ($this->captchaObjectType) {
                $this->captchaObjectType->getProcessor()->reset();
            }
        }

        $responses = $comment->responses;
        if (
            $this->commentProcessor->canModerate(
                $comment->objectTypeID,
                $comment->objectID
            )
        ) {
            $responses = $comment->unfilteredResponses;
        }

        return [
            'commentID' => $comment->commentID,
            'template' => $this->renderResponse($this->createdResponse),
            'responses' => $responses + 1,
        ];
    }

    /**
     * Publishes a response.
     */
    public function triggerPublicationResponse()
    {
        if (!empty($this->parameters['commentProcessor'])) {
            $objectType = null;
            if (!empty($this->parameters['responses'])) {
                /** @var CommentResponse $response */
                $response = \reset($this->parameters['responses']);
                $objectType = $this->validateObjectType($response->getComment()->objectTypeID);
            }

            $this->commentProcessor = $this->parameters['commentProcessor'];
        } else {
            $objectType = $this->validateObjectType($this->parameters['objectTypeID']);
            $this->commentProcessor = $objectType->getProcessor();
        }

        /** @var CommentResponse $response */
        foreach ($this->parameters['responses'] as $response) {
            (new CommentResponseEditor($response))->update(['isDisabled' => 0]);

            $comment = $response->getComment();

            // update response count
            $commentEditor = new CommentEditor($comment);
            $commentEditor->updateCounters(['responses' => 1]);

            // do not prepend the response id as the approved response can appear anywhere
            $commentEditor->updateResponseIDs();

            // update counter
            $this->commentProcessor->updateCounter($comment->objectID, 1);

            // fire activity event
            if ($response->userID && UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType . '.response.recentActivityEvent')) {
                UserActivityEventHandler::getInstance()->fireEvent(
                    $objectType->objectType . '.response.recentActivityEvent',
                    $response->responseID,
                    null,
                    $response->userID,
                    $response->time
                );
            }

            // fire notification event
            if (
                UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType . '.notification')
                && (
                    UserNotificationHandler::getInstance()->getEvent($objectType->objectType . '.response.notification', 'commentResponse')
                    || UserNotificationHandler::getInstance()->getEvent($objectType->objectType . '.response.notification', 'commentResponseOwner')
                )
            ) {
                $notificationObject = new CommentResponseUserNotificationObject($response);
                $notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType . '.notification');
                \assert($notificationObjectType instanceof ICommentUserNotificationObjectType);

                if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
                    $recipientIDs = $notificationObjectType->getRecipientIDs($comment);
                } else {
                    $recipientIDs = [];
                }

                $recipientIDs[] = $comment->userID;

                $userID = $notificationObjectType->getOwnerID($comment->commentID);

                // make sure that the response's author gets no notification
                $recipientIDs = \array_diff($recipientIDs, [$response->getUserID()]);

                if (UserNotificationHandler::getInstance()->getEvent($objectType->objectType . '.response.notification', 'commentResponse')) {
                    UserNotificationHandler::getInstance()->fireEvent(
                        'commentResponse',
                        $objectType->objectType . '.response.notification',
                        $notificationObject,
                        $recipientIDs,
                        [
                            'commentID' => $comment->commentID,
                            'objectID' => $comment->objectID,
                            'userID' => $comment->userID,
                        ]
                    );
                }

                // notify the container owner
                if (UserNotificationHandler::getInstance()->getEvent($objectType->objectType . '.response.notification', 'commentResponseOwner')) {
                    if ($userID != $comment->userID && $userID != $response->getUserID()) {
                        UserNotificationHandler::getInstance()->fireEvent(
                            'commentResponseOwner',
                            $objectType->objectType . '.response.notification',
                            $notificationObject,
                            [$userID],
                            [
                                'commentID' => $comment->commentID,
                                'objectID' => $comment->objectID,
                                'objectUserID' => $userID,
                                'userID' => $comment->userID,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Validates the `enable` action.
     *
     * @throws  PermissionDeniedException
     */
    public function validateEnable()
    {
        $this->comment = $this->getSingleObject()->getDecoratedObject();

        $objectType = $this->validateObjectType($this->comment->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->canModerate($this->comment->objectTypeID, $this->comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Enables a comment.
     *
     * @return  int[]
     */
    public function enable()
    {
        if ($this->comment === null) {
            $this->comment = \reset($this->objects);
        }

        if ($this->comment->isDisabled) {
            $action = new self([$this->comment], 'triggerPublication', [
                'commentProcessor' => $this->commentProcessor,
                'objectTypeID' => $this->comment->objectTypeID,
            ]);
            $action->executeAction();
        }

        ModerationQueueActivationManager::getInstance()->removeModeratedContent(
            'com.woltlab.wcf.comment.comment',
            [$this->comment->commentID]
        );

        return ['commentID' => $this->comment->commentID];
    }

    /**
     * Validates the `enableResponse` action.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @deprecated 6.0 use `CommentResponseAction::validateEnable()` instead
     */
    public function validateEnableResponse()
    {
        $this->readInteger('responseID', false, 'data');
        $this->response = new CommentResponse($this->parameters['data']['responseID']);
        if (!$this->response->responseID) {
            throw new UserInputException('responseID');
        }

        $this->comment = $this->response->getComment();

        $objectType = $this->validateObjectType($this->comment->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->canModerate($this->comment->objectTypeID, $this->comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Enables a response.
     *
     * @return  int[]
     * @deprecated 6.0 use `CommentResponseAction::enable()` instead
     */
    public function enableResponse()
    {
        if ($this->comment === null) {
            $this->comment = \reset($this->objects);
        }
        if ($this->response === null) {
            $this->response = \reset($this->parameters['responses']);
        }

        if ($this->response->isDisabled) {
            $action = new self([], 'triggerPublicationResponse', [
                'commentProcessor' => $this->commentProcessor,
                'objectTypeID' => $this->comment->objectTypeID,
                'responses' => [$this->response],
            ]);
            $action->executeAction();
        }

        ModerationQueueActivationManager::getInstance()->removeModeratedContent(
            'com.woltlab.wcf.comment.response',
            [$this->response->responseID]
        );

        return ['responseID' => $this->response->responseID];
    }

    /**
     * @inheritDoc
     */
    public function validateBeginEdit()
    {
        $this->comment = $this->getSingleObject()->getDecoratedObject();

        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID);
        $processor = $objectType->getProcessor();
        if (!$processor->canEditComment($this->comment)) {
            throw new PermissionDeniedException();
        }

        $this->setDisallowedBBCodes();
    }

    /**
     * @inheritDoc
     */
    public function beginEdit()
    {
        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($this->comment->message, 'com.woltlab.wcf.comment');
        WCF::getTPL()->assign([
            'comment' => $this->comment,
            'text' => $upcastProcessor->getHtml(),
            'wysiwygSelector' => 'commentEditor' . $this->comment->commentID,
        ]);

        return [
            'actionName' => 'beginEdit',
            'template' => WCF::getTPL()->fetch('commentEditor', 'wcf'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateSave()
    {
        $this->validateBeginEdit();

        $this->validateMessage();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        /** @var HtmlInputProcessor $htmlInputProcessor */
        $htmlInputProcessor = $this->parameters['htmlInputProcessor'];

        $data = [
            'message' => $htmlInputProcessor->getHtml(),
        ];

        $htmlInputProcessor->setObjectID($this->comment->getObjectID());
        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()->registerObjects($htmlInputProcessor);
        if ($this->comment->hasEmbeddedObjects != $hasEmbeddedObjects) {
            $data['hasEmbeddedObjects'] = $this->comment->hasEmbeddedObjects ? 0 : 1;
        }

        $action = new self([$this->comment], 'update', [
            'data' => $data,
        ]);
        $action->executeAction();

        $comment = new Comment($this->comment->getObjectID());

        if ($comment->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment',
                [$comment->getObjectID()]
            );
        }

        return [
            'actionName' => 'save',
            'message' => $comment->getFormattedMessage(),
        ];
    }

    /**
     * Validates parameters to remove a comment or response.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @deprecated 6.0 use `delete()` instead
     */
    public function validateRemove()
    {
        // validate comment id or response id
        try {
            $this->validateCommentID();
        } catch (UserInputException $e) {
            try {
                $this->validateResponseID();
            } catch (UserInputException $e) {
                throw new UserInputException('objectIDs');
            }
        }

        // validate object type id
        $objectType = $this->validateObjectType();

        // validate object id and permissions
        $this->commentProcessor = $objectType->getProcessor();
        if ($this->comment !== null) {
            if (!$this->commentProcessor->canDeleteComment($this->comment)) {
                throw new PermissionDeniedException();
            }
        } else {
            if (!$this->commentProcessor->canDeleteResponse($this->response)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Removes a comment or response.
     *
     * @return  int[]
     * @deprecated 6.0 use `delete()` instead
     */
    public function remove()
    {
        if ($this->comment !== null) {
            $objectAction = new self([$this->comment], 'delete');
            $objectAction->executeAction();

            return ['commentID' => $this->comment->commentID];
        } else {
            $objectAction = new CommentResponseAction([$this->response], 'delete');
            $objectAction->executeAction();

            return ['responseID' => $this->response->responseID];
        }
    }

    /**
     * @since 6.0
     */
    private function validateGetGuestDialog(): void
    {
        if (!WCF::getUser()->userID) {
            if (isset($this->parameters['data']['username'])) {
                $this->validateUsername();
                $this->validateCaptcha();
            }
        }
    }

    /**
     * Returns the dialog for guests when they try to write a comment letting
     * them enter a username and solving a captcha.
     *
     * @throws  SystemException
     */
    private function getGuestDialog(): ?string
    {
        if (WCF::getUser()->userID) {
            return null;
        }

        if (isset($this->parameters['data']['username']) && empty($this->validationErrors)) {
            return null;
        }

        if (!empty($this->validationErrors)) {
            if (!empty($this->parameters['data']['username'])) {
                WCF::getSession()->register('username', $this->parameters['data']['username']);
            }
            WCF::getTPL()->assign('errorType', $this->validationErrors);
        }

        $captchaObjectType = null;

        if (CAPTCHA_TYPE) {
            $captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName(CAPTCHA_TYPE);
            if ($captchaObjectType === null) {
                throw new SystemException("Unknown captcha object type with name '" . CAPTCHA_TYPE . "'");
            }

            if (!$captchaObjectType->getProcessor()->isAvailable()) {
                $captchaObjectType = null;
            }
        }

        return WCF::getTPL()->fetch('commentAddGuestDialog', 'wcf', [
            'ajaxCaptcha' => true,
            'captchaID' => 'commentAdd',
            'captchaObjectType' => $captchaObjectType,
            'supportsAsyncCaptcha' => true,
            'username' => WCF::getSession()->getVar('username'),
        ]);
    }

    /**
     * Renders a comment.
     *
     * @param Comment $comment
     * @param CommentResponse $response
     * @return  string|string[]
     */
    protected function renderComment(Comment $comment, ?CommentResponse $response = null)
    {
        $comment = new StructuredComment($comment);
        $comment->setIsDeletable($this->commentProcessor->canDeleteComment($comment->getDecoratedObject()));
        $comment->setIsEditable($this->commentProcessor->canEditComment($comment->getDecoratedObject()));

        if ($comment->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment',
                [$comment->getObjectID()]
            );
        }
        if ($response && $response->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment.response',
                [$response->getObjectID()]
            );
        }

        if ($response !== null) {
            // check if response is not visible
            /** @var CommentResponse $visibleResponse */
            foreach ($comment as $visibleResponse) {
                if ($visibleResponse->responseID == $response->responseID) {
                    $response = null;
                    break;
                }
            }
        }

        // This functions renders a single comment without rendering its responses.
        // We need to prevent the setting of the data attribute for the last response time
        // so that the loading of the responses by the user works correctly.
        if ($comment->getDecoratedObject()->responses) {
            WCF::getTPL()->assign('ignoreLastResponseTime', true);
        }

        WCF::getTPL()->assign([
            'commentCanAdd' => $this->commentProcessor->canAdd(
                $comment->getDecoratedObject()->objectID
            ),
            'commentCanModerate' => $this->commentProcessor->canModerate(
                $comment->getDecoratedObject()->objectTypeID,
                $comment->getDecoratedObject()->objectID
            ),
            'commentList' => [$comment],
            'commentManager' => $this->commentProcessor,
        ]);

        // load like data
        if (MODULE_LIKE) {
            $likeData = [];
            $commentObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment');
            ReactionHandler::getInstance()->loadLikeObjects($commentObjectType, [$comment->commentID]);
            $likeData['comment'] = ReactionHandler::getInstance()->getLikeObjects($commentObjectType);

            $responseIDs = [];
            foreach ($comment as $visibleResponse) {
                $responseIDs[] = $visibleResponse->responseID;
            }

            if ($response !== null) {
                $responseIDs[] = $response->responseID;
            }

            if (!empty($responseIDs)) {
                $responseObjectType = ReactionHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
                ReactionHandler::getInstance()->loadLikeObjects($responseObjectType, $responseIDs);
                $likeData['response'] = ReactionHandler::getInstance()->getLikeObjects($responseObjectType);
            }

            WCF::getTPL()->assign('likeData', $likeData);
        }

        $template = WCF::getTPL()->fetch('commentList');
        if ($response === null) {
            return $template;
        }

        return [
            'template' => $template,
            'response' => $this->renderResponse($response),
        ];
    }

    /**
     * Renders a response.
     *
     * @param CommentResponse $response
     * @return  string
     */
    protected function renderResponse(CommentResponse $response)
    {
        $response = new StructuredCommentResponse($response);
        $response->setIsDeletable($this->commentProcessor->canDeleteResponse($response->getDecoratedObject()));
        $response->setIsEditable($this->commentProcessor->canEditResponse($response->getDecoratedObject()));

        if ($response->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment.response',
                [$response->getObjectID()]
            );
        }

        // render response
        WCF::getTPL()->assign([
            'responseList' => [$response],
            'commentCanModerate' => $this->commentProcessor->canModerate(
                $response->getComment()->objectTypeID,
                $response->getComment()->objectID
            ),
            'commentManager' => $this->commentProcessor,
        ]);

        return WCF::getTPL()->fetch('commentResponseList');
    }

    /**
     * Validates message parameters.
     *
     * @throws      UserInputException
     */
    protected function validateMessage(bool $isResponse = false)
    {
        $this->readString('message', false, 'data');
        $this->parameters['data']['message'] = MessageUtil::stripCrap($this->parameters['data']['message']);

        if (empty($this->parameters['data']['message'])) {
            throw new UserInputException('message');
        }

        CommentHandler::enforceCensorship($this->parameters['data']['message']);

        $this->setDisallowedBBCodes();

        $htmlInputProcessor = new HtmlInputProcessor();
        if ($isResponse) {
            $htmlInputProcessor->process(
                $this->parameters['data']['message'],
                'com.woltlab.wcf.comment.response'
            );
        } else {
            $htmlInputProcessor->process(
                $this->parameters['data']['message'],
                'com.woltlab.wcf.comment',
                $this->comment !== null ? $this->comment->getObjectID() : 0
            );
        }

        // search for disallowed bbcodes
        $disallowedBBCodes = $htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            throw new UserInputException(
                'text',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.disallowedBBCodes',
                    ['disallowedBBCodes' => $disallowedBBCodes]
                )
            );
        }

        if ($htmlInputProcessor->appearsToBeEmpty()) {
            throw new UserInputException('message');
        }

        $commentTextContent = $htmlInputProcessor->getTextContent();
        if (\mb_strlen($commentTextContent) > WCF::getSession()->getPermission('user.comment.maxLength')) {
            throw new UserInputException(
                'text',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.tooLong',
                    ['maxTextLength' => WCF::getSession()->getPermission('user.comment.maxLength')]
                )
            );
        }

        $this->parameters['htmlInputProcessor'] = $htmlInputProcessor;
    }

    /**
     * Validates object type id parameter.
     *
     * @param int $objectTypeID
     * @return  ObjectType
     * @throws  UserInputException
     */
    protected function validateObjectType($objectTypeID = null)
    {
        if ($objectTypeID === null) {
            $this->readInteger('objectTypeID', false, 'data');
            $objectTypeID = $this->parameters['data']['objectTypeID'];
        }

        $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
        if ($objectType === null) {
            throw new UserInputException('objectTypeID');
        }
        if ($objectType->getDefinition()->definitionName !== 'com.woltlab.wcf.comment.commentableContent') {
            throw new UserInputException('objectTypeID');
        }

        return $objectType;
    }

    /**
     * Validates comment id parameter.
     *
     * @throws  UserInputException
     * @deprecated 6.0 obsolete
     */
    protected function validateCommentID()
    {
        $this->readInteger('commentID', false, 'data');

        $this->comment = new Comment($this->parameters['data']['commentID']);
        if ($this->comment === null || !$this->comment->commentID) {
            throw new UserInputException('commentID');
        }
    }

    /**
     * Validates response id parameter.
     *
     * @throws  UserInputException
     * @deprecated 6.0 obsolete
     */
    protected function validateResponseID()
    {
        if (isset($this->parameters['data']['responseID'])) {
            $this->response = new CommentResponse($this->parameters['data']['responseID']);
        }
        if ($this->response === null || !$this->response->responseID) {
            throw new UserInputException('responseID');
        }
    }

    /**
     * Validates the username parameter.
     */
    protected function validateUsername()
    {
        if (WCF::getUser()->userID) {
            return;
        }

        try {
            $this->readString('username', false, 'data');

            if (!UserRegistrationUtil::isValidUsername($this->parameters['data']['username'])) {
                throw new UserInputException('username', 'invalid');
            }
            if (User::getUserByUsername($this->parameters['data']['username'])->userID) {
                throw new UserInputException('username', 'notUnique');
            }
        } catch (UserInputException $e) {
            $this->validationErrors['username'] = $e->getType();
        }
    }

    /**
     * Validates the captcha challenge.
     *
     * @throws  SystemException
     */
    protected function validateCaptcha()
    {
        if (WCF::getUser()->userID) {
            return;
        }

        if (CAPTCHA_TYPE) {
            $this->captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName(CAPTCHA_TYPE);
            if ($this->captchaObjectType === null) {
                throw new SystemException("Unknown captcha object type with name '" . CAPTCHA_TYPE . "'");
            }

            if (!$this->captchaObjectType->getProcessor()->isAvailable()) {
                $this->captchaObjectType = null;
            }
        }

        if ($this->captchaObjectType === null) {
            return;
        }

        try {
            $this->captchaObjectType->getProcessor()->readFormParameters();
            $this->captchaObjectType->getProcessor()->validate();
        } catch (UserInputException $e) {
            $this->validationErrors = \array_merge($this->validationErrors, [$e->getField() => $e->getType()]);
        }
    }

    /**
     * Sets the list of disallowed bbcodes for comments.
     */
    protected function setDisallowedBBCodes()
    {
        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission('user.comment.disallowedBBCodes')
        ));
    }

    /**
     * Returns the current html input processor or a new one if `$message` is not null.
     *
     * @param string|null $message source message
     * @param int $objectID object id
     * @return      HtmlInputProcessor
     */
    public function getHtmlInputProcessor($message = null, $objectID = 0)
    {
        if ($message === null) {
            return $this->htmlInputProcessor;
        }

        $this->htmlInputProcessor = new HtmlInputProcessor();
        $this->htmlInputProcessor->process($message, 'com.woltlab.wcf.comment', $objectID);

        return $this->htmlInputProcessor;
    }

    /**
     * Returns the comment object.
     *
     * @return  Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Returns the comment response object.
     *
     * @return  CommentResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the comment manager.
     *
     * @return  ICommentManager
     */
    public function getCommentManager()
    {
        return $this->commentProcessor;
    }
}

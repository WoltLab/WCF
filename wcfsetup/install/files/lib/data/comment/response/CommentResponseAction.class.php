<?php

namespace wcf\data\comment\response;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\WCF;
use wcf\util\MessageUtil;

/**
 * Executes comment response-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  CommentResponse         create()
 * @method  CommentResponseEditor[]     getObjects()
 * @method  CommentResponseEditor       getSingleObject()
 */
class CommentResponseAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['loadResponses'];

    /**
     * @inheritDoc
     */
    protected $className = CommentResponseEditor::class;

    /**
     * comment object
     * @var Comment
     * @deprecated 6.1
     */
    public $comment;

    /**
     * comment manager object
     * @var ICommentManager
     * @deprecated 6.1
     */
    public $commentManager;

    /**
     * comment processor
     * @var ICommentManager
     * @deprecated 6.1
     */
    protected $commentProcessor;

    /**
     * @var HtmlInputProcessor
     * @deprecated 6.1
     */
    protected $htmlInputProcessor;

    /**
     * response object
     * @var CommentResponse
     * @deprecated 6.1
     */
    protected $response;

    /**
     * Validates parameters to load responses for a given comment id.
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function validateLoadResponses()
    {
        $this->readInteger('commentID', false, 'data');
        $this->readInteger('lastResponseTime', false, 'data');
        $this->readInteger('lastResponseID', true, 'data');

        $this->comment = new Comment($this->parameters['data']['commentID']);
        if (!$this->comment->commentID) {
            throw new UserInputException('commentID');
        }

        $this->commentManager = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID)->getProcessor();
        if (!$this->commentManager->isAccessible($this->comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns parsed responses for given comment id.
     *
     * @return  array
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function loadResponses()
    {
        $commentCanModerate = $this->commentManager->canModerate(
            $this->comment->objectTypeID,
            $this->comment->objectID
        );

        // get response list
        $responseList = new StructuredCommentResponseList($this->commentManager, $this->comment);
        if ($this->parameters['data']['lastResponseID']) {
            $responseList->getConditionBuilder()->add(
                "(comment_response.time > ? OR (comment_response.time = ? && comment_response.responseID > ?))",
                [
                    $this->parameters['data']['lastResponseTime'],
                    $this->parameters['data']['lastResponseTime'],
                    $this->parameters['data']['lastResponseID'],
                ]
            );
        } else {
            $responseList->getConditionBuilder()->add(
                "comment_response.time > ?",
                [$this->parameters['data']['lastResponseTime']]
            );
        }
        if (!$commentCanModerate) {
            $responseList->getConditionBuilder()->add("comment_response.isDisabled = ?", [0]);
        }
        $responseList->readObjects();

        $lastResponseTime = $lastResponseID = 0;
        foreach ($responseList as $response) {
            if (!$lastResponseTime) {
                $lastResponseTime = $response->time;
            }
            if (!$lastResponseID) {
                $lastResponseID = $response->responseID;
            }

            $lastResponseTime = \max($lastResponseTime, $response->time);
            $lastResponseID = \max($lastResponseID, $response->responseID);
        }

        // mark notifications for loaded responses as read
        CommentHandler::getInstance()->markNotificationsAsConfirmedForResponses(
            CommentHandler::getInstance()->getObjectType($this->comment->objectTypeID)->objectType,
            $responseList->getObjects()
        );

        WCF::getTPL()->assign([
            'commentCanModerate' => $commentCanModerate,
            'likeData' => MODULE_LIKE ? $responseList->getLikeData() : [],
            'responseList' => $responseList,
            'commentManager' => $this->commentManager,
        ]);

        return [
            'commentID' => $this->comment->commentID,
            'lastResponseTime' => $lastResponseTime,
            'lastResponseID' => $lastResponseID,
            'template' => WCF::getTPL()->fetch('commentResponseList'),
        ];
    }

    /**
     * @inheritDoc
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function validateBeginEdit()
    {
        $this->response = $this->getSingleObject();

        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->response->getComment()->objectTypeID);
        $this->commentProcessor = $objectType->getProcessor();
        if (!$this->commentProcessor->canEditResponse($this->response->getDecoratedObject())) {
            throw new PermissionDeniedException();
        }

        $this->setDisallowedBBCodes();
    }

    /**
     * @inheritDoc
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function beginEdit()
    {
        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($this->response->message, 'com.woltlab.wcf.comment.response');
        WCF::getTPL()->assign([
            'response' => $this->response,
            'text' => $upcastProcessor->getHtml(),
            'wysiwygSelector' => 'commentResponseEditor' . $this->response->responseID,
        ]);

        return [
            'actionName' => 'beginEdit',
            'template' => WCF::getTPL()->fetch('commentResponseEditor', 'wcf'),
        ];
    }

    /**
     * @inheritDoc
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function validateSave()
    {
        $this->validateBeginEdit();

        $this->validateMessage();
    }

    /**
     * @inheritDoc
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function save()
    {
        /** @var HtmlInputProcessor $htmlInputProcessor */
        $htmlInputProcessor = $this->parameters['htmlInputProcessor'];

        $data = [
            'message' => $htmlInputProcessor->getHtml(),
        ];

        $htmlInputProcessor->setObjectID($this->response->getObjectID());
        $hasEmbeddedObjects = MessageEmbeddedObjectManager::getInstance()->registerObjects($htmlInputProcessor);
        if ($this->response->hasEmbeddedObjects != $hasEmbeddedObjects) {
            $data['hasEmbeddedObjects'] = $this->response->hasEmbeddedObjects ? 0 : 1;
        }

        (new self([$this->response], 'update', [
            'data' => $data,
        ]))->executeAction();

        $response = new CommentResponse($this->response->getObjectID());

        if ($response->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'com.woltlab.wcf.comment.response',
                [$response->getObjectID()]
            );
        }

        return [
            'actionName' => 'save',
            'message' => $response->getFormattedMessage(),
        ];
    }

    /**
     * Validates message parameter.
     *
     * @throws      UserInputException
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    protected function validateMessage()
    {
        $this->readString('message', false, 'data');
        $this->parameters['data']['message'] = MessageUtil::stripCrap($this->parameters['data']['message']);

        if (empty($this->parameters['data']['message'])) {
            throw new UserInputException('message');
        }

        CommentHandler::enforceCensorship($this->parameters['data']['message']);

        $this->setDisallowedBBCodes();
        $htmlInputProcessor = $this->getHtmlInputProcessor(
            $this->parameters['data']['message'],
            $this->response !== null ? $this->response->getObjectID() : 0
        );

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

        $this->parameters['htmlInputProcessor'] = $htmlInputProcessor;
    }

    /**
     * Validates object type id parameter.
     *
     * @param int $objectTypeID
     * @return  ObjectType
     * @throws  UserInputException
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
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

        return $objectType;
    }

    /**
     * Sets the list of disallowed bbcodes for comments.
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
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
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function getHtmlInputProcessor($message = null, $objectID = 0)
    {
        if ($message === null) {
            return $this->htmlInputProcessor;
        }

        $this->htmlInputProcessor = new HtmlInputProcessor();
        $this->htmlInputProcessor->process($message, 'com.woltlab.wcf.comment.response', $objectID);

        return $this->htmlInputProcessor;
    }

    /**
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @since   6.0
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function validateEnable(): void
    {
        $this->readObjects();

        if ($this->getObjects() === []) {
            throw new UserInputException('objectIDs');
        }

        foreach ($this->getObjects() as $response) {
            if (!$response->isDisabled) {
                throw new UserInputException('objectIDs');
            }

            $comment = $response->getComment();
            $objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
            $processor = $objectType->getProcessor();
            if (!$processor->canModerate($objectType->objectTypeID, $comment->objectID)) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @since 6.0
     * @deprecated 6.1 see https://docs.woltlab.com/6.1/migration/wsc60/php/#comment-backend
     */
    public function enable(): void
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        if (empty($this->objects)) {
            return;
        }

        foreach ($this->getObjects() as $response) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($response->getComment()->objectTypeID);

            (new CommentAction([], 'triggerPublicationResponse', [
                'commentProcessor' => $objectType->getProcessor(),
                'objectTypeID' => $objectType->objectTypeID,
                'responses' => [$response->getDecoratedObject()],
            ]))->executeAction();
        }

        ModerationQueueActivationManager::getInstance()->removeModeratedContent(
            'com.woltlab.wcf.comment.response',
            $this->getObjectIDs()
        );
    }
}

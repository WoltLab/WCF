<?php

namespace wcf\system\worker;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Worker implementation for updating comment responses.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CommentResponseRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 500;

    /**
     * @inheritDoc
     */
    protected $objectListClassName = CommentResponseList::class;

    private HtmlInputProcessor $htmlInputProcessor;

    #[\Override]
    public function execute()
    {
        parent::execute();

        if (\count($this->getObjectList()) === 0) {
            return;
        }

        // retrieve permissions
        $userIDs = [];
        foreach ($this->objectList as $response) {
            $userIDs[] = $response->userID;
        }
        $userPermissions = $this->getBulkUserPermissions($userIDs, ['user.comment.disallowedBBCodes']);

        WCF::getDB()->beginTransaction();
        /** @var CommentResponse $response */
        foreach ($this->objectList as $response) {
            $responseEditor = new CommentResponseEditor($response);

            BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                ',',
                $this->getBulkUserPermissionValue($userPermissions, $response->userID, 'user.comment.disallowedBBCodes')
            ));

            $data = [];

            // update message
            if (!$response->enableHtml) {
                $this->getHtmlInputProcessor()->process(
                    $response->message,
                    'com.woltlab.wcf.comment.response',
                    $response->responseID,
                    true
                );

                $data['enableHtml'] = 1;
            } else {
                $this->getHtmlInputProcessor()->reprocess(
                    $response->message,
                    'com.woltlab.wcf.comment.response',
                    $response->responseID
                );
            }

            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor(), true)) {
                $data['hasEmbeddedObjects'] = 1;
            } else {
                $data['hasEmbeddedObjects'] = 0;
            }

            $data['message'] = $this->getHtmlInputProcessor()->getHtml();
            $responseEditor->update($data);
        }
        WCF::getDB()->commitTransaction();
    }

    private function getHtmlInputProcessor(): HtmlInputProcessor
    {
        if (!isset($this->htmlInputProcessor)) {
            $this->htmlInputProcessor = new HtmlInputProcessor();
        }

        return $this->htmlInputProcessor;
    }
}

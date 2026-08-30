<?php

namespace wcf\system\worker;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentBuilder;
use wcf\data\comment\CommentList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Worker implementation for updating comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractLinearRebuildDataWorker<CommentList>
 */
final class CommentRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 500;

    /**
     * @inheritDoc
     */
    protected $objectListClassName = CommentList::class;

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
        foreach ($this->objectList as $comment) {
            $userIDs[] = $comment->userID;
        }
        $userPermissions = $this->getBulkUserPermissions($userIDs, ['user.comment.disallowedBBCodes']);

        WCF::getDB()->beginTransaction();
        /** @var Comment $comment */
        foreach ($this->objectList as $comment) {
            $builder = CommentBuilder::forUpdate($comment)
                ->recalculateResponseIDs()
                ->recalculateUnfilteredResponseIDs();

            BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                ',',
                $this->getBulkUserPermissionValue($userPermissions, $comment->userID, 'user.comment.disallowedBBCodes')
            ));

            // update message
            if ($comment->enableHtml === 0) {
                $this->getHtmlInputProcessor()->process(
                    $comment->message,
                    'com.woltlab.wcf.comment',
                    $comment->commentID,
                    true
                );

                $builder->setEnableHtml(true);
            } else {
                $this->getHtmlInputProcessor()->reprocess(
                    $comment->message,
                    'com.woltlab.wcf.comment',
                    $comment->commentID
                );
            }

            $builder->setHasEmbeddedObjects(
                MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor(), true)
            );

            $builder
                ->setMessage($this->getHtmlInputProcessor()->getHtml())
                ->update();
        }
        WCF::getDB()->commitTransaction();

        MessageEmbeddedObjectManager::getInstance()->commitBulkOperation();
    }

    private function getHtmlInputProcessor(): HtmlInputProcessor
    {
        if (!isset($this->htmlInputProcessor)) {
            $this->htmlInputProcessor = new HtmlInputProcessor();
        }

        return $this->htmlInputProcessor;
    }
}

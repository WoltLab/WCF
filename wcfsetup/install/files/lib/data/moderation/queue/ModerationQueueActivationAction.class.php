<?php

namespace wcf\data\moderation\queue;

use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes actions for reports.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Moderation\Queue
 */
class ModerationQueueActivationAction extends ModerationQueueAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['enableContent', 'removeContent'];

    /**
     * moderation queue editor object
     * @var ModerationQueueEditor
     */
    public $queue;

    /**
     * Validates parameters to enable content.
     */
    public function validateEnableContent()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $moderationQueueEditor) {
            if (!$moderationQueueEditor->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Enables content.
     */
    public function enableContent()
    {
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $moderationQueueEditor) {
            ModerationQueueActivationManager::getInstance()->enableContent(
                $moderationQueueEditor->getDecoratedObject()
            );

            $moderationQueueEditor->markAsConfirmed();
        }
        WCF::getDB()->commitTransaction();

        $this->unmarkItems();
    }

    /**
     * Validates parameters to delete reported content.
     */
    public function validateRemoveContent()
    {
        $this->readString('message', true);
        $this->validateEnableContent();

        if (!ModerationQueueActivationManager::getInstance()->canRemoveContent($this->queue->getDecoratedObject())) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Deletes reported content.
     */
    public function removeContent()
    {
        // mark content as deleted
        ModerationQueueActivationManager::getInstance()->removeContent(
            $this->queue->getDecoratedObject(),
            $this->parameters['message']
        );

        $this->queue->markAsRejected();

        $this->unmarkItems();
    }

    /**
     * Validates the `removeActivationContent` action.
     *
     * @since   5.4
     */
    public function validateRemoveActivationContent(): void
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $moderationQueueEditor) {
            if (
                !$moderationQueueEditor->canEdit()
                || !ModerationQueueActivationManager::getInstance()->canRemoveContent($moderationQueueEditor->getDecoratedObject())
            ) {
                throw new PermissionDeniedException();
            }
        }

        $this->parameters['message'] = StringUtil::trim($this->parameters['message'] ?? '');
    }

    /**
     * Deletes disabled content via clipboard.
     *
     * @since   5.4
     */
    public function removeActivationContent(): void
    {
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $moderationQueueEditor) {
            ModerationQueueActivationManager::getInstance()->removeContent(
                $moderationQueueEditor->getDecoratedObject(),
                $this->parameters['message']
            );

            $moderationQueueEditor->markAsConfirmed();
        }
        WCF::getDB()->commitTransaction();

        $this->unmarkItems();
    }
}

<?php

namespace wcf\data\moderation\queue;

use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\flood\FloodControl;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;
use wcf\util\HtmlString;
use wcf\util\StringUtil;

/**
 * Executes actions for reports.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ModerationQueueReportAction extends ModerationQueueAction
{
    private const ALLOWED_REPORTS_PER_10M = 10;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['prepareReport', 'removeContent', 'removeReport', 'report'];

    /**
     * moderation queue editor object
     * @var ModerationQueueEditor
     */
    public $queue;

    /**
     * Validates parameters to delete reported content.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateRemoveContent()
    {
        $this->validateRemoveReport();

        foreach ($this->getObjects() as $moderationQueueEditor) {
            if (!ModerationQueueReportManager::getInstance()->canRemoveContent($moderationQueueEditor->getDecoratedObject())) {
                throw new PermissionDeniedException();
            }
        }

        $this->parameters['message'] = isset($this->parameters['message']) ? StringUtil::trim($this->parameters['message']) : '';
    }

    /**
     * Deletes reported content.
     *
     * @return void
     *
     * @deprecated 6.3 use API endpoint `DeleteContent` instead
     */
    public function removeContent()
    {
        foreach ($this->getObjects() as $moderationQueueEditor) {
            ModerationQueueReportManager::getInstance()->removeContent(
                $moderationQueueEditor->getDecoratedObject(),
                $this->parameters['message']
            );

            $moderationQueueEditor->markAsConfirmed();
        }
    }

    /**
     * Validates parameters to mark this report as done.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateRemoveReport()
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

        if (isset($this->parameters['data']['markAsJustified'])) {
            $this->readBoolean('markAsJustified', true, 'data');

            // Clipboard passes `markAsJustified` in the `data` array.
            $this->parameters['markAsJustified'] = $this->parameters['data']['markAsJustified'];
        } else {
            $this->readBoolean('markAsJustified', true);
        }
    }

    /**
     * Removes this report by marking it as done without further processing.
     *
     * @return void
     *
     * @deprecated 6.3 use API endpoint `CloseReport` instead
     */
    public function removeReport()
    {
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $moderationQueueEditor) {
            $moderationQueueEditor->markAsRejected($this->parameters['markAsJustified'] ?? false);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Validates parameters to prepare a report.
     *
     * @return void
     */
    public function validatePrepareReport()
    {
        WCF::getSession()->checkPermissions(['user.profile.canReportContent']);

        $this->readInteger('objectID');
        $this->readString('objectType');

        if (!ModerationQueueReportManager::getInstance()->isValid($this->parameters['objectType'])) {
            throw new UserInputException('objectType');
        }

        // validate the combination of object type and object id
        if (
            !ModerationQueueReportManager::getInstance()->isValid(
                $this->parameters['objectType'],
                $this->parameters['objectID']
            )
        ) {
            throw new UserInputException('objectID');
        }

        // validate if user may read the content (prevent information disclosure by reporting random ids)
        if (
            !ModerationQueueReportManager::getInstance()->canReport(
                $this->parameters['objectType'],
                $this->parameters['objectID']
            )
        ) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Prepares a report.
     *
     * @return array{alreadyReported: 0|1, template: string}
     */
    public function prepareReport()
    {
        // content was already reported
        $alreadyReported = ModerationQueueReportManager::getInstance()->hasPendingReport(
            $this->parameters['objectType'],
            $this->parameters['objectID']
        ) ? 1 : 0;

        return [
            'alreadyReported' => $alreadyReported,
            'template' => WCF::getTPL()->render('wcf', 'moderationReportDialog', [
                'alreadyReported' => $alreadyReported,
                'object' => ModerationQueueReportManager::getInstance()->getReportedObject(
                    $this->parameters['objectType'],
                    $this->parameters['objectID']
                ),
            ]),
        ];
    }

    /**
     * Validates parameters for reporting.
     *
     * @return void
     */
    public function validateReport()
    {
        WCF::getSession()->checkPermissions(['user.profile.canReportContent']);

        $this->readString('message');
        if (\mb_strlen($this->parameters['message']) > 64000) {
            // we allow only up to 64.000 characters (~1.5 below TEXT maximum)
            $this->parameters['message'] = \mb_substr($this->parameters['messages'], 0, 64000);
        }

        $requests = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.moderation.report',
            new \DateInterval('PT10M')
        );
        if ($requests['count'] >= self::ALLOWED_REPORTS_PER_10M) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.page.error.flood')
            ));
        }

        $this->validatePrepareReport();
    }

    /**
     * Reports an item.
     *
     * @return array{reported: 1}
     */
    public function report()
    {
        // if the specified content was already reported, e.g. a different user reported this
        // item meanwhile, silently ignore it. Just display a success and the user is happy :)
        if (
            !ModerationQueueReportManager::getInstance()->hasPendingReport(
                $this->parameters['objectType'],
                $this->parameters['objectID']
            )
        ) {
            ModerationQueueReportManager::getInstance()->addReport(
                $this->parameters['objectType'],
                $this->parameters['objectID'],
                $this->parameters['message']
            );
        }

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.moderation.report');

        return [
            'reported' => 1,
        ];
    }

    /**
     * Validates the `changeJustifiedStatus` action.
     *
     * @since   5.4
     *
     * @deprecated 6.3
     */
    public function validateChangeJustifiedStatus(): void
    {
        $this->queue = $this->getSingleObject();
        if (!$this->queue->canEdit() || !$this->queue->canChangeJustifiedStatus()) {
            throw new PermissionDeniedException();
        }

        $this->readBoolean('markAsJustified', true);
    }

    /**
     * Updates the `markAsJustified` status.
     *
     * @deprecated 6.3 use API endpoint `ChangeJustifiedStatus` instead
     */
    public function changeJustifiedStatus(): void
    {
        $additionalData = $this->queue->additionalData;
        $additionalData['markAsJustified'] = $this->parameters['markAsJustified'];

        $this->queue->update([
            'additionalData' => \serialize($additionalData),
        ]);
    }

    /**
     * Validates the `removeReportContent` action.
     *
     * @since   5.4
     *
     * @deprecated 6.3
     */
    public function validateRemoveReportContent(): void
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
                || !ModerationQueueReportManager::getInstance()->canRemoveContent($moderationQueueEditor->getDecoratedObject())
            ) {
                throw new PermissionDeniedException();
            }
        }

        $this->parameters['message'] = StringUtil::trim($this->parameters['message'] ?? '');
    }

    /**
     * Deletes reported content via clipboard.
     *
     * @since   5.4
     *
     * @deprecated 6.3 use API endpoint `DeleteContent` instead
     */
    public function removeReportContent(): void
    {
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $moderationQueueEditor) {
            ModerationQueueReportManager::getInstance()->removeContent(
                $moderationQueueEditor->getDecoratedObject(),
                $this->parameters['message']
            );

            $moderationQueueEditor->markAsConfirmed();
        }
        WCF::getDB()->commitTransaction();
    }
}

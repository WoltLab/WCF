<?php

namespace wcf\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\moderation\queue\ModerationQueueReportAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * Clipboard action implementation for report moderation queue entries.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Clipboard\Action
 * @since   5.4
 */
class ModerationQueueReportClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $actionClassActions = [
        'removeReport',
        'removeReportContent',
    ];

    /**
     * @inheritDoc
     */
    protected $reloadPageOnSuccess = [
        'removeReport',
        'removeReportContent',
    ];

    /**
     * @inheritDoc
     */
    protected $supportedActions = [
        'removeReport',
        'removeReportContent',
    ];

    /**
     * @inheritDoc
     */
    public function execute(array $objects, ClipboardAction $action)
    {
        $item = parent::execute($objects, $action);

        if ($item === null) {
            return;
        }

        switch ($action->actionName) {
            case 'removeReportContent':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.moderation.report.removeContent.confirmMessage',
                        [
                            'moderationQueueCount' => $item->getCount(),
                        ]
                    )
                );
                $item->addInternalData(
                    'template',
                    WCF::getTPL()->fetch('moderationReportRemoveContent')
                );
                break;

            case 'removeReport':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.moderation.report.removeReport.confirmMessage',
                        [
                            'moderationQueueCount' => $item->getCount(),
                        ]
                    )
                );
                $item->addInternalData(
                    'template',
                    WCF::getTPL()->fetch('moderationReportRemoveReport')
                );
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return ModerationQueueReportAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.moderation.queue';
    }

    /**
     * Returns the ids of the ids of the marked moderation queue entries that are report
     * moderation queue entries whose content the active user can delete.
     *
     * @return  int[]
     */
    public function validateRemoveReportContent(): array
    {
        return \array_values(\array_filter(\array_map(static function (ViewableModerationQueue $queue) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID);
            /** @var IModerationQueueReportHandler $processor */
            $processor = $objectType->getProcessor();
            $definition = $objectType->getDefinition();
            if (
                $definition->definitionName === 'com.woltlab.wcf.moderation.report'
                && $queue->canEdit()
                && $processor->canRemoveContent($queue->getDecoratedObject())
                && !$queue->isDone()
            ) {
                return $queue->queueID;
            }
        }, $this->objects)));
    }

    /**
     * Returns the ids of the ids of the marked moderation queue entries that are report
     * moderation queue entries the active user can close.
     *
     * @return  int[]
     */
    public function validateRemoveReport(): array
    {
        return \array_values(\array_filter(\array_map(static function (ViewableModerationQueue $queue) {
            $definition = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->getDefinition();
            if (
                $definition->definitionName === 'com.woltlab.wcf.moderation.report'
                && $queue->canEdit()
                && !$queue->isDone()
            ) {
                return $queue->queueID;
            }
        }, $this->objects)));
    }
}

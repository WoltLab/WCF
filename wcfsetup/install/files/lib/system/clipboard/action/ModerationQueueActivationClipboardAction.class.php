<?php

namespace wcf\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\moderation\queue\ModerationQueueActivationAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * Clipboard action implementation for activation moderation queue entries.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Clipboard\Action
 * @since   5.4
 */
class ModerationQueueActivationClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $actionClassActions = [
        'enableContent',
        'removeActivationContent',
    ];

    /**
     * @inheritDoc
     */
    protected $reloadPageOnSuccess = [
        'enableContent',
        'removeActivationContent',
    ];

    /**
     * @inheritDoc
     */
    protected $supportedActions = [
        'enableContent',
        'removeActivationContent',
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
            case 'enableContent':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.moderation.activation.enableContent.confirmMessage',
                        [
                            'moderationQueueCount' => $item->getCount(),
                        ]
                    )
                );
                break;

            case 'removeActivationContent':
                $item->addInternalData(
                    'confirmMessage',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.moderation.activation.removeContent.confirmMessage',
                        [
                            'moderationQueueCount' => $item->getCount(),
                        ]
                    )
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
        return ModerationQueueActivationAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.moderation.queue';
    }

    /**
     * Returns the ids of the ids of the marked moderation queue entries that are activation
     * moderation queue entries whose content the active user can delete.
     *
     * @return  int[]
     */
    public function validateRemoveActivationContent(): array
    {
        return \array_values(\array_filter(\array_map(static function (ViewableModerationQueue $queue) {
            $objectType = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID);
            /** @var IModerationQueueReportHandler $processor */
            $processor = $objectType->getProcessor();
            $definition = $objectType->getDefinition();
            if (
                $definition->definitionName === 'com.woltlab.wcf.moderation.activation'
                && $queue->canEdit()
                && $processor->canRemoveContent($queue->getDecoratedObject())
                && !$queue->isDone()
            ) {
                return $queue->queueID;
            }
        }, $this->objects)));
    }

    /**
     * Returns the ids of the ids of the marked moderation queue entries that are activation
     * moderation queue entries whose content the active user can enable.
     *
     * @return  int[]
     */
    public function validateEnableContent(): array
    {
        return \array_values(\array_filter(\array_map(static function (ViewableModerationQueue $queue) {
            $definition = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->getDefinition();
            if (
                $definition->definitionName === 'com.woltlab.wcf.moderation.activation'
                && $queue->canEdit()
                && !$queue->isDone()
            ) {
                return $queue->queueID;
            }
        }, $this->objects)));
    }
}

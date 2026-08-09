<?php

namespace wcf\system\interaction\bulk\user;

use wcf\action\ModerationQueueAssignUserAction;
use wcf\action\ModerationReportQueueCloseAction;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\interaction\bulk\user\ModerationQueueBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkFormBuilderDialogInteraction;
use wcf\system\interaction\bulk\BulkRpcInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\WCF;

/**
 * Bulk interaction provider for moderation queue.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ModerationQueueBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('mod.general.canUseModeration')) {
            return;
        }

        $this->addInteractions([
            new BulkRpcInteraction(
                "mark-as-read",
                "core/moderation-queues/%s/mark-as-read",
                "wcf.global.button.markAsRead",
                isAvailableCallback: static function (ModerationQueue $queue) {
                    $viewableQueue = new ViewableModerationQueue($queue);
                    return $viewableQueue->isNew();
                }
            ),
            new BulkFormBuilderDialogInteraction(
                "assign-user",
                ModerationQueueAssignUserAction::class,
                "wcf.moderation.assignedUser.change",
                static fn(ModerationQueue $queue) => $queue->canEdit()
            ),
            new BulkFormBuilderDialogInteraction(
                "close",
                ModerationReportQueueCloseAction::class,
                "wcf.moderation.report.removeReport",
                isAvailableCallback: static function (ModerationQueue $queue) {
                    return self::isReportQueue($queue)
                        && $queue->canEdit()
                        && !$queue->isDone();
                }
            ),
            new BulkRpcInteraction(
                "remove-content",
                "core/moderation-queues/%s/delete-content",
                "wcf.moderation.report.removeContent",
                InteractionConfirmationType::SoftDeleteWithReason,
                isAvailableCallback: static function (ModerationQueue $queue) {
                    $objectType = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID);
                    $processor = $objectType->getProcessor();
                    \assert($processor instanceof IModerationQueueHandler);

                    return $queue->canEdit()
                        && !$queue->isDone()
                        && $processor->canRemoveContent($queue);
                }
            ),
            new BulkRpcInteraction(
                "enable",
                "core/moderation-queues/%s/enable-content",
                "wcf.moderation.activation.enableContent",
                InteractionConfirmationType::Custom,
                "wcf.moderation.activation.enableContent.confirmMessage",
                isAvailableCallback: static function (ModerationQueue $queue) {
                    return self::isActivationQueue($queue)
                        && $queue->canEdit()
                        && !$queue->isDone();
                }
            )
        ]);

        EventHandler::getInstance()->fire(
            new ModerationQueueBulkInteractionCollecting($this)
        );
    }

    private static function isReportQueue(ModerationQueue $queue): bool
    {
        $definition = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->getDefinition();

        return $definition->definitionName === 'com.woltlab.wcf.moderation.report';
    }

    private static function isActivationQueue(ModerationQueue $queue): bool
    {
        $definition = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID)->getDefinition();

        return $definition->definitionName === 'com.woltlab.wcf.moderation.activation';
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return ModerationQueueList::class;
    }
}

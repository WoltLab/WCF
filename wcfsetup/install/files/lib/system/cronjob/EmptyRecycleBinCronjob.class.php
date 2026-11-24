<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\event\moderation\TrashedContentPrunersCollecting;
use wcf\system\event\EventHandler;

/**
 * Permanently deletes content that has been in the trash for longer than the configured period.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class EmptyRecycleBinCronjob extends AbstractCronjob
{
    /**
     * Maximum number of objects each pruner deletes per execution.
     */
    private const BATCH_SIZE = 100;

    #[\Override]
    public function execute(Cronjob $cronjob): void
    {
        parent::execute($cronjob);

        if (\MESSAGE_EMPTY_RECYCLE_BIN_CYCLE === 0) {
            return;
        }

        $deletedBefore = \TIME_NOW - \MESSAGE_EMPTY_RECYCLE_BIN_CYCLE * 86_400;

        $event = new TrashedContentPrunersCollecting();
        EventHandler::getInstance()->fire($event);

        foreach ($event->getPruners() as $pruner) {
            try {
                $pruner->prune($deletedBefore, self::BATCH_SIZE);
            } catch (\Throwable $e) {
                // A failing pruner must not prevent the remaining pruners from running.
                \wcf\functions\exception\logThrowable($e);
            }
        }
    }
}

<?php

namespace wcf\system\view\moderation;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\package\PackageCache;
use wcf\event\moderation\queue\SpamDetectionRendering;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Represents the notice and the spam detections of a moderation queue entry that was
 * filed because the content is suspected spam.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SuspectedSpamView
{
    public function __construct(
        public readonly ViewableModerationQueue $queue,
    ) {}

    public function render(): string
    {
        $detections = [];
        foreach ($this->queue->getSpamDetections() as $identifier => $data) {
            $event = new SpamDetectionRendering($this->queue, $identifier, $data);
            EventHandler::getInstance()->fire($event);

            $package = PackageCache::getInstance()->getPackageByIdentifier($identifier);
            $packageName = $package?->getName() ?? '';
            $details = $event->getDetails();
            if ($packageName === '' && $details === null) {
                continue;
            }

            $detections[] = [
                'packageName' => $packageName,
                'details' => $details,
            ];
        }

        return WCF::getTPL()->render('wcf', 'moderationSuspectedSpam', [
            'detections' => $detections,
        ]);
    }
}

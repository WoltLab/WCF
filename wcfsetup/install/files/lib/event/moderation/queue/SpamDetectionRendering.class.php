<?php

namespace wcf\event\moderation\queue;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\event\IPsr14Event;
use wcf\util\HtmlString;

/**
 * Indicates that a spam detection recorded for a moderation queue entry is being rendered
 * for the moderators. Fired once per detection, the listener of the detecting package
 * `$identifier` may provide the details of its detection.
 *
 * `$data` is read from the moderation queue entry. Validate it before use and escape any
 * value taken from it, the details are output as-is.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SpamDetectionRendering implements IPsr14Event
{
    private ?HtmlString $details = null;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        public readonly ViewableModerationQueue $queue,
        public readonly string $identifier,
        public readonly array $data,
    ) {}

    public function getDetails(): ?HtmlString
    {
        return $this->details;
    }

    public function setDetails(HtmlString $details): void
    {
        $this->details = $details;
    }
}

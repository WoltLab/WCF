<?php

namespace wcf\event\message;

use wcf\data\user\User;
use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Indicates that a new message by a user is currently validated. If this event is interrupted,
 * the message is considered to be spam.
 *
 * Listeners should flag the message with `markAsSpam()`, which records the detection for the
 * moderators, instead of calling `preventDefault()` directly.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class MessageSpamChecking implements IInterruptableEvent
{
    use TInterruptableEvent;

    /**
     * @var array<string, array<array-key, mixed>>
     */
    private array $detections = [];

    public function __construct(
        public readonly HtmlInputProcessor $processor,
        public readonly ?User $user = null,
        public readonly string $ipAddress = '',
        public readonly string $subject = '',
    ) {
    }

    /**
     * Flags the message as spam and records the detection of the package `$identifier`,
     * e.g. `com.woltlab.wcf`. A later detection by the same package replaces the former.
     *
     * `$data` must consist of scalar values and arrays only: it is stored serialized in
     * the moderation queue entry and read back with `unserialize()`, which would revive
     * objects as whatever their class looks like at that time, if it still exists.
     *
     * @param array<array-key, mixed> $data
     * @since 6.3
     */
    public function markAsSpam(string $identifier, array $data = []): void
    {
        $this->detections[$identifier] = $data;

        $this->preventDefault();
    }

    /**
     * Returns the detections recorded with `markAsSpam()`, keyed by the package identifier
     * of the detecting package. The list is empty if the message is not considered to be
     * spam, but also when a listener called `preventDefault()` without recording a detection.
     *
     * @return array<string, array<array-key, mixed>>
     * @since 6.3
     */
    public function getDetections(): array
    {
        return $this->detections;
    }
}

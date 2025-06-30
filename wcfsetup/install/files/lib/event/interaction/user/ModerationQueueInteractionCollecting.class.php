<?php

namespace wcf\event\interaction\user;

use wcf\event\IPsr14Event;
use wcf\system\interaction\user\ModerationQueueInteractions;

/**
 * Indicates that the provider for moderation queue interactions is collecting interactions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ModerationQueueInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly ModerationQueueInteractions $provider) {}
}

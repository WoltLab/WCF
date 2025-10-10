<?php

namespace wcf\system\moderation\queue\command;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\user\User;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\moderation\queue\AssignUser` instead.
 */
final class AssignUser
{
    public function __construct(
        private readonly ModerationQueue $moderationQueue,
        private readonly ?User $user,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\moderation\queue\AssignUser(
            $this->moderationQueue,
            $this->user
        ))();
    }
}

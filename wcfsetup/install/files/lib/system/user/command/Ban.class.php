<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;

/**
 * Ban a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class Ban
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly ?int $banExpires = null,
    ) {
    }

    public function __invoke(): void
    {
        $editor = new UserEditor($this->user);
        $editor->update([
            'banned' => 1,
            'banReason' => $this->reason,
            'banExpires' => $this->banExpires ?? 0,
        ]);
    }
}

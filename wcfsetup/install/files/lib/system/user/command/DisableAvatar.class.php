<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\util\DateUtil;

/**
 * Disable a user's avatar.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableAvatar
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly ?int $expires = null,
    ) {
    }

    public function __invoke(): void
    {
        $expires = null;
        if ($this->expires !== null) {
            $expires = DateUtil::getDateTimeByTimestamp($this->expires)->format('Y-m-d');
        }

        (new UserAction([$this->user], 'disableAvatar', [
            'disableAvatarReason' => $this->reason,
            'disableAvatarExpires' => $expires,
        ]))->executeAction();
    }
}

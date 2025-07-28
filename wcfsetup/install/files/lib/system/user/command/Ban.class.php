<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\util\DateUtil;

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
        $banExpires = null;
        if ($this->banExpires !== null) {
            $banExpires = DateUtil::getDateTimeByTimestamp($this->banExpires)->format('Y-m-d');
        }

        (new UserAction([$this->user], 'ban', [
            'banReason' => $this->reason,
            'banExpires' => $banExpires,
        ]))->executeAction();
    }
}

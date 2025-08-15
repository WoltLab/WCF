<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserSignatureDisabled;
use wcf\system\event\EventHandler;

/**
 * Disable a user's signature.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableSignature
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        private readonly int $expires = 0,
    ) {}

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableSignature' => 1,
            'disableSignatureReason' => $this->reason,
            'disableSignatureExpires' => $this->expires,
        ]);

        $event = new UserSignatureDisabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}

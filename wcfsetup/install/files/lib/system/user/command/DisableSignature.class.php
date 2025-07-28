<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;

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
        private readonly ?int $banExpires = null,
    ) {
    }

    public function __invoke(): void
    {
        $editor = new UserEditor($this->user);
        $editor->update([
            'disableSignature' => 1,
            'disableSignatureReason' => $this->reason,
            'disableSignatureExpires' => $this->banExpires ?? 0,
        ]);
    }
}

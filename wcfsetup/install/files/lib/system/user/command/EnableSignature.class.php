<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\user\UserSignatureEnabled;
use wcf\system\event\EventHandler;

/**
 * Enable a user's signature.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableSignature
{
    public function __construct(private readonly User $user)
    {
    }

    public function __invoke(): void
    {
        (new UserEditor($this->user))->update([
            'disableSignature' => 0,
        ]);

        $event = new UserSignatureEnabled($this->user);
        EventHandler::getInstance()->fire($event);
    }
}

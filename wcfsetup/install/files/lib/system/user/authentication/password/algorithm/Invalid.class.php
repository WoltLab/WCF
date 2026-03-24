<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Always indicates that the password is invalid.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class Invalid implements IPasswordAlgorithm
{
    #[\Override]
    public function verify(
        #[\SensitiveParameter]
        string $password,
        string $hash
    ): bool {
        return false;
    }

    #[\Override]
    public function hash(
        #[\SensitiveParameter]
        string $password
    ): string {
        return '';
    }

    #[\Override]
    public function needsRehash(string $hash): bool
    {
        return false;
    }
}

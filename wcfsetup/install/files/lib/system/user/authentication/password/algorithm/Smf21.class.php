<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for Simple Machines Forum 2.1 (smf21).
 *
 * SMF 2.1 hashes the lowercased username followed by the password with bcrypt. The
 * lowercased username is stored as the salt after the hash, separated by a colon.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.2.10
 */
final class Smf21 implements IPasswordAlgorithm
{
    /**
     * @inheritDoc
     */
    public function verify(
        #[\SensitiveParameter]
        string $password,
        string $hash
    ): bool {
        $parts = \explode(':', $hash, 2);
        $hash = $parts[0];
        $salt = $parts[1] ?? '';

        return \password_verify($salt . $password, $hash);
    }

    /**
     * @inheritDoc
     */
    public function hash(
        #[\SensitiveParameter]
        string $password
    ): string {
        // bcrypt only considers the first 72 bytes, a salt would cut into them.
        return \password_hash($password, \PASSWORD_BCRYPT);
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return false;
    }
}

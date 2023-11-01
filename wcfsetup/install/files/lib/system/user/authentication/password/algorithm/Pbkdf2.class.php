<?php

namespace wcf\system\user\authentication\password\algorithm;

use ParagonIE\ConstantTime\Hex;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the pbkdf2 password algorithm.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 */
final class Pbkdf2 implements IPasswordAlgorithm
{
    /**
     * @inheritDoc
     */
    public function verify(
        #[\SensitiveParameter]
        string $password,
        string $hash
    ): bool {
        $parts = \explode(':', $hash, 5);
        if (\count($parts) !== 5) {
            return false;
        }
        [$hash, $salt, $algo, $iterations, $length] = $parts;

        return \hash_equals($hash, \bin2hex(\hash_pbkdf2($algo, $password, $salt, $iterations, $length, true)));
    }

    /**
     * @inheritDoc
     */
    public function hash(
        #[\SensitiveParameter]
        string $password
    ): string {
        $salt = Hex::encode(\random_bytes(20));
        $algo = 'sha256';
        $iterations = 600000;
        $length = 32;
        $hash = \bin2hex(\hash_pbkdf2($algo, $password, $salt, $iterations, $length, true));

        return \implode(':', [$hash, $salt, $algo, $iterations, $length]);
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return false;
    }
}

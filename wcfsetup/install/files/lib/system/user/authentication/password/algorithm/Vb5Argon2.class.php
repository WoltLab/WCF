<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation for the vBulletin 5 Argon2 implementation which requires md5-prehashing.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since   5.4
 */
final class Vb5Argon2 implements IPasswordAlgorithm
{
    /**
     * @var Argon2
     */
    private $argon2;

    /**
     * Wcf2 constructor.
     */
    public function __construct()
    {
        $this->argon2 = new Argon2();
    }

    /**
     * @inheritDoc
     */
    public function verify(string $password, string $hash): bool
    {
        return $this->argon2->verify(\md5($password), $hash);
    }

    /**
     * @inheritDoc
     */
    public function hash(string $password): string
    {
        return $this->argon2->hash(\md5($password));
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return $this->argon2->needsRehash($hash);
    }
}

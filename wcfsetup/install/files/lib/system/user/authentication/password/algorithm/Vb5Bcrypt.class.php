<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation for the vBulletin 5 BCrypt/Blowfish implementation which requires md5-prehashing.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since   5.4
 */
final class Vb5Bcrypt implements IPasswordAlgorithm
{
    /**
     * @var Bcrypt
     */
    private $bcrypt;

    /**
     * Wcf2 constructor.
     */
    public function __construct()
    {
        $this->bcrypt = new Bcrypt();
    }

    /**
     * @inheritDoc
     */
    public function verify(string $password, string $hash): bool
    {
        return $this->bcrypt->verify(\md5($password), $hash);
    }

    /**
     * @inheritDoc
     */
    public function hash(string $password): string
    {
        return $this->bcrypt->hash(\md5($password));
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return $this->bcrypt->needsRehash($hash);
    }
}

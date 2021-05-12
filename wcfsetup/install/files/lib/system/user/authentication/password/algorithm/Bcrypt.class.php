<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of BCrypt.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since   5.4
 */
final class Bcrypt implements IPasswordAlgorithm
{
    /**
     * @var int
     */
    private $cost;

    /**
     * @param int $cost The BCrypt 'cost' option for newly created hashes. It is recommended not to change this option.
     */
    public function __construct(int $cost = 12)
    {
        if ($cost < 9) {
            throw new \InvalidArgumentException(\sprintf(
                "Refusing to accept BCrypt costs lower than '9', '%d' given.",
                $cost
            ));
        }
        if ($cost > 14) {
            throw new \InvalidArgumentException(\sprintf(
                "Refusing to accept BCrypt costs higher than '14', '%d' given.",
                $cost
            ));
        }

        $this->cost = $cost;
    }

    /**
     * @inheritDoc
     */
    public function verify(string $password, string $hash): bool
    {
        return \password_verify($password, $hash);
    }

    /**
     * @inheritDoc
     */
    public function hash(string $password): string
    {
        return \password_hash($password, \PASSWORD_BCRYPT, $this->getOptions());
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return \password_needs_rehash($hash, \PASSWORD_BCRYPT, $this->getOptions());
    }

    /**
     * Returns the value to be used for password_*'s `$options` parameter.
     */
    private function getOptions()
    {
        return [
            'cost' => $this->cost,
        ];
    }
}

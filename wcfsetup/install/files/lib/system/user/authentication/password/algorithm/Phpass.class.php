<?php

namespace wcf\system\user\authentication\password\algorithm;

use ParagonIE\ConstantTime\Hex;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the PHPASS password algorithm.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since   5.4
 */
final class Phpass implements IPasswordAlgorithm
{
    use TPhpass;

    private const COSTS = 10;

    /**
     * @inheritDoc
     */
    public function hash(string $password): string
    {
        $salt = Hex::encode(\random_bytes(4));

        return $this->hashPhpass($password, $this->getSettings() . $salt) . ':';
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return !\str_starts_with($hash, $this->getSettings());
    }

    /**
     * Returns the settings prefix with the algorithm identifier and costs.
     */
    private function getSettings(): string
    {
        return '$H$' . $this->itoa64[self::COSTS];
    }
}

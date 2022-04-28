<?php

namespace wcf\system\user\authentication\password\algorithm;

use ParagonIE\ConstantTime\Hex;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for Drupal 8.x.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since   5.4
 */
final class Drupal8 implements IPasswordAlgorithm
{
    use TPhpass;

    /**
     * Returns the hashed password, with the given settings.
     */
    private function hashDrupal(string $password, string $settings): string
    {
        $output = '*';

        // Check for correct hash
        if (\mb_substr($settings, 0, 3, '8bit') !== '$S$') {
            return $output;
        }

        $count_log2 = \mb_strpos($this->itoa64, $settings[3], 0, '8bit');

        if ($count_log2 < 7 || $count_log2 > 30) {
            return $output;
        }

        $count = 1 << $count_log2;
        $salt = \mb_substr($settings, 4, 8, '8bit');

        if (\mb_strlen($salt, '8bit') != 8) {
            return $output;
        }

        $hash = \hash('sha512', $salt . $password, true);
        do {
            $hash = \hash('sha512', $hash . $password, true);
        } while (--$count);

        $output = \mb_substr($settings, 0, 12, '8bit');
        $output .= $this->encode64($hash, 64);

        return \mb_substr($output, 0, 55, '8bit');
    }

    /**
     * @inheritDoc
     */
    public function verify(string $password, string $hash): bool
    {
        // The passwords are stored differently when importing. Sometimes they are saved with the salt,
        // but sometimes also without the salt. We don't need the salt, because the salt is saved with the hash.
        [$hash] = \explode(':', $hash, 2);

        return \hash_equals($hash, $this->hashDrupal($password, $hash));
    }

    /**
     * @inheritDoc
     */
    public function hash(string $password): string
    {
        $settings = '$S$D';
        $settings .= Hex::encode(\random_bytes(4));

        return $this->hashDrupal($password, $settings) . ':';
    }

    /**
     * @inheritDoc
     */
    public function needsRehash(string $hash): bool
    {
        return false;
    }
}

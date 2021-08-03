<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\exception\NotImplementedException;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for phpBB 3.x (phpbb3).
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since       5.4
 */
final class Phpbb3 implements IPasswordAlgorithm
{
    use TPhpass {
        verify as phpassVerify;

        hash as phpassHash;
    }

    public function verify(string $password, string $hash): bool
    {
        if ($this->phpassVerify($password, $hash)) {
            return true;
        }

        if (!\preg_match('/^\$([^$]+)\$/', $hash, $matches)) {
            return false;
        }

        $algorithms = \explode('\\', $matches[1]);

        // Strip the type prefix.
        $hash = \substr($hash, \strlen($matches[0]));

        // The following loop only supports the multi-hash variant.
        // Everything else should already be handled at this point.
        if (\count($algorithms) == 1) {
            return false;
        }

        foreach ($algorithms as $algorithm) {
            $dollar = \strpos($hash, '$');
            if ($dollar === false) {
                return false;
            }

            $settings = '$' . $algorithm . '$' . \str_replace('\\', '$', \substr($hash, 0, $dollar));
            $hash = \substr($hash, $dollar + 1);

            switch ($algorithm) {
                case 'H':
                case 'P':
                    $password = \str_replace($settings, '', $this->hashPhpass($password, $settings));
                    break;
                case '2a':
                case '2y':
                    $password = \str_replace($settings, '', \crypt($password, $settings));
                    break;
            }
        }

        return \hash_equals($hash, $password);
    }

    public function needsRehash(string $hash): bool
    {
        return false;
    }

    public function hash(string $password): string
    {
        throw new NotImplementedException();
    }
}

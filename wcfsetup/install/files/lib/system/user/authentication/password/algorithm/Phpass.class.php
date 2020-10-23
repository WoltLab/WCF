<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the PHPASS password algorithm.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Phpass implements IPasswordAlgorithm {
	use TPhpass;
}

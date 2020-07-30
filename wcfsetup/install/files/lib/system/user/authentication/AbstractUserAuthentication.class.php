<?php
namespace wcf\system\user\authentication;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a user authentication.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication
 */
abstract class AbstractUserAuthentication extends SingletonFactory implements IUserAuthentication {}

<?php
namespace wcf\system\user\authentication;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of an user authentication.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.authentication
 * @category	Community Framework
 */
abstract class AbstractUserAuthentication extends SingletonFactory implements IUserAuthentication {}

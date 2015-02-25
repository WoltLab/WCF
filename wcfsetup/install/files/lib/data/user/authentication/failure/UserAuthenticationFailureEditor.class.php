<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user authentication failures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.authentication.failure
 * @category	Community Framework
 */
class UserAuthenticationFailureEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\authentication\failure\UserAuthenticationFailure';
}

<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user authentication failures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Authentication\Failure
 * 
 * @method static	UserAuthenticationFailure	create(array $parameters = [])
 * @method		UserAuthenticationFailure	getDecoratedObject()
 * @mixin		UserAuthenticationFailure
 */
class UserAuthenticationFailureEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserAuthenticationFailure::class;
}

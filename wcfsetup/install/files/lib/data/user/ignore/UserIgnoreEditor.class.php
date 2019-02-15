<?php
namespace wcf\data\user\ignore;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ignored users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Ignore
 * 
 * @method static	UserIgnore	create(array $parameters = [])
 * @method		UserIgnore	getDecoratedObject()
 * @mixin		UserIgnore
 */
class UserIgnoreEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserIgnore::class;
}

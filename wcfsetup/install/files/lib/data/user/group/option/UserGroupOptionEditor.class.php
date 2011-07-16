<?php
namespace wcf\data\user\group\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit usergroup options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option
 * @category 	Community Framework
 */
class UserGroupOptionEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\group\option\UserGroupOption';
}

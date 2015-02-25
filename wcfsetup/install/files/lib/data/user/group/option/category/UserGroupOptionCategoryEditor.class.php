<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit usergroup option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 */
class UserGroupOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\user\group\option\category\UserGroupOptionCategory';
}

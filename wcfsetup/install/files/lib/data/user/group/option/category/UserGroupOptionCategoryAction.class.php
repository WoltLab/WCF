<?php
namespace wcf\data\user\group\option\category;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user group option category-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 */
class UserGroupOptionCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\group\option\category\UserGroupOptionCategoryEditor';
}

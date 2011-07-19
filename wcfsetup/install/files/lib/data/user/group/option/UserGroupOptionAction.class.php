<?php
namespace wcf\data\user\group\option;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user group option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option
 * @category 	Community Framework
 */
class UserGroupOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\group\option\UserGroupOptionEditor';
}

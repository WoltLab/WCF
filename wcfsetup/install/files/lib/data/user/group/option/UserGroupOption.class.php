<?php
namespace wcf\data\user\group\option;
use wcf\data\option\Option;

/**
 * Represents a usergroup option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Option
 * 
 * @property-read	integer		$usersOnly
 */
class UserGroupOption extends Option {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_group_option';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'optionID';
}

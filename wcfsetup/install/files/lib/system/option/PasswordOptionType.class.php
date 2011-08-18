<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * PasswordOptionType is an implementation of IOptionType for 'input type="password"' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class PasswordOptionType extends TextOptionType {
	/**
	 * @see wcf\system\option\TextOptionType::$inputType
	 */
	protected $inputType = 'password';
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		return false;
	}
}

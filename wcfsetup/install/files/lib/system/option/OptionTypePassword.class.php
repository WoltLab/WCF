<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\option\OptionTypeText;

/**
 * OptionTypeText is an implementation of OptionType for 'input type="password"' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class OptionTypePassword extends OptionTypeText {
	/**
	 * @see wcf\system\option\OptionTypeText::$inputType
	 */
	protected $inputType = 'password';
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		return false;
	}
}

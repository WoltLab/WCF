<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Any searchable option type should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
interface ISearchableUserOption {
	/**
	 * Returns the html code for the search form element of this option.
	 * 
	 * @param	\wcf\data\option\Option	$option
	 * @param	string			$value
	 * @return	string			html
	 */
	public function getSearchFormElement(Option $option, $value);
	
	/**
	 * Returns a condition for search sql query.
	 * 
	 * @param	\wcf\system\database\condition\PreparedStatementConditionBuilder	$conditions
	 * @param	\wcf\data\option\Option							$option
	 * @param	mixed									$value
	 * @return	boolean
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value);
}

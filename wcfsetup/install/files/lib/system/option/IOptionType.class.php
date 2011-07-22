<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Any option type should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
interface IOptionType {
	/**
	 * Returns the html code for the form element of this option.
	 * 
	 * @param	Option		$option
	 * @param	mixed		$value
	 * @return	string		html
	 */
	public function getFormElement(Option $option, $value);
	
	/**
	 * Validates the form input for this option.
	 * Throws an exception, if validation fails.
	 * 
	 * @param	Option		$option
	 * @param	string		$newValue
	 */
	public function validate(Option $option, $newValue);
	
	/**
	 * Returns the value of this option for saving in the database.
	 * 
	 * @param	Option		$option
	 * @param	string		$newValue
	 * @return	string
	 */
	public function getData(Option $option, $newValue);
}

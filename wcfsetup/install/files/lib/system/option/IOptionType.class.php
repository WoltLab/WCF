<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Any option type has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
interface IOptionType {
	/**
	 * Returns the html code of the form element for the given option of this
	 * option type.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @param	mixed				$value
	 * @return	string
	 */
	public function getFormElement(Option $option, $value);
	
	/**
	 * Validates the input for the given option of this option type and throws
	 * a wcf\system\exception\UserInputException if the validation should fail.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @param	string				$newValue
	 */
	public function validate(Option $option, $newValue);
	
	/**
	 * Returns the value of the given option of this option type which will
	 * be saved in the database.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 * @param	string				$newValue
	 * @return	string
	 */
	public function getData(Option $option, $newValue);
	
	/**
	 * Returns the css class name for this option type. 
	 * 
	 * @return	string
	 */
	public function getCSSClassName();
	
	/**
	 * Returns true if options supports internationalization .
	 * 
	 * @return	boolean
	 */
	public function supportI18n();
	
	/**
	 * Compares two values and returns a PHP-like comparison result.
	 * 
	 *   $value1 < $value2	=> -1
	 *   $value1 == $value2	=> 0
	 *   $value1 > $value2	=> 1
	 * 
	 * 
	 * @param	mixed		$value1
	 * @param	mixed		$value2
	 * @return	integer
	 */
	public function compare($value1, $value2);
	
	/**
	 * Returns true if option's label is hidden in search form.
	 * 
	 * @return	boolean
	 */
	public function hideLabelInSearch();
}

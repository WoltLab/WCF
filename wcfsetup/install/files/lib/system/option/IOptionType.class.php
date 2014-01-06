<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Any option type has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
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
}

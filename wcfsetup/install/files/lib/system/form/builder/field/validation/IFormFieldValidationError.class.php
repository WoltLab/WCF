<?php
namespace wcf\system\form\builder\field\validation;

/**
 * Represents an error that occurred during the validation of a form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Validation
 * @since	5.2
 */
interface IFormFieldValidationError {
	/**
	 * Initializes a new validation error.
	 * 
	 * If no language item is given, `wcf.global.form.error.{$type}` is used.
	 * 
	 * @param	string		$type		error type
	 * @param	null|string	$languageItem	language item containing the error message
	 * @param	array		$information	additional error information, also used to resolve error message from language item
	 * 
	 * @throws	\InvalidArgumentException	if the given error type is invalid
	 */
	public function __construct($type, $languageItem = null, array $information = []);
	
	/**
	 * Returns the HTML element representing the error.
	 * 
	 * @return	string
	 */
	public function getHtml();
	
	/**
	 * Returns additional information about the error.
	 * 
	 * @return	array		additional error information
	 */
	public function getInformation();
	
	/**
	 * Returns the message describing the validation error.
	 * 
	 * @return	string		error message
	 */
	public function getMessage();
	
	/**
	 * Returns the type of the validation error.
	 * 
	 * @return	string		error type
	 */
	public function getType();
}

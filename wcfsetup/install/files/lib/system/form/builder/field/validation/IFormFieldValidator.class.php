<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field\validation;
use wcf\system\form\builder\field\IFormField;

/**
 * Validates the value of a form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Validation
 * @since	3.2
 */
interface IFormFieldValidator {
	/**
	 * Initializes a new validator.
	 * 
	 * @param	string		$id		id of the validator
	 * @param	callable	$validator	validation function
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function __construct(string $id, callable $validator);
	
	/**
	 * Validates the value of the given field.
	 * 
	 * @param	IFormField	$field		validated field
	 */
	public function __invoke(IFormField $field);
	
	/**
	 * Returns the id of the validator.
	 * 
	 * @return	string		id of the dependency
	 */
	public function getId();
	
	/**
	 * Checks if the given parameter is a string and a valid validator id.
	 * 
	 * @param	mixed		$id		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public static function validateId(string $id);
}

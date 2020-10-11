<?php
namespace wcf\system\form\builder\field\validation;
use wcf\system\form\builder\field\IFormField;

/**
 * Validates the value of a form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Validation
 * @since	5.2
 */
class FormFieldValidator implements IFormFieldValidator {
	/**
	 * id of the validator that has to be unique for each field
	 * @var	
	 */
	protected $id;
	
	/**
	 * validation function
	 * @var	callable
	 */
	protected $validator;
	
	/**
	 * @inheritDoc
	 */
	public function __construct($id, callable $validator) {
		static::validateId($id);
		
		$this->id = $id;
		
		// validate validation function
		$parameters = (new \ReflectionFunction($validator))->getParameters();
		if (count($parameters) !== 1) {
			throw new \InvalidArgumentException("The validation function must expect one parameter, instead " . count($parameters) . " parameters are expected.");
		}
		
		if (PHP_MAJOR_VERSION >= 8) {
			$parameterType = $parameters[0]->getType();
		}
		else {
			$parameterType = $parameters[0]->getClass();
		}
		
		if ($parameterType === null || ($parameterType->getName() !== IFormField::class && !is_subclass_of($parameterType->getName(), IFormField::class))) {
			throw new \InvalidArgumentException(
				"The validation function's parameter must be an instance of '" . IFormField::class . "', instead " .
				($parameterType === null ? 'any' : "'" . $parameterType->getName() . "'") . " parameter is expected."
			);
		}
		
		$this->validator = $validator;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __invoke(IFormField $field) {
		call_user_func($this->validator, $field);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function validateId($id) {
		if (preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
	}
}

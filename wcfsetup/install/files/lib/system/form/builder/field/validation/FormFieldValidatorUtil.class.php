<?php
namespace wcf\system\form\builder\field\validation;
use wcf\system\form\builder\field\IFormField;
use wcf\system\Regex;

/**
 * Contains form field validator-related functions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Validation
 * @since	3.2
 */
abstract class FormFieldValidatorUtil {
	/**
	 * Returns a form field validator to check the form field value against
	 * the given regular expression.
	 * 
	 * @param	string		$regularExpression	regular expression used to validate form field value
	 * @param	string		$languageItemPrefix	language item prefix used for error language item `{$languageItemPrefix}.error.format`
	 * @return	IFormFieldValidator
	 * 
	 * @throws	\InvalidArgumentException		if regular expression is invalid
	 */
	public static function getRegularExpressionValidator($regularExpression, $languageItemPrefix) {
		$regex = Regex::compile($regularExpression);
		if (!$regex->isValid()) {
			throw new \InvalidArgumentException("Invalid regular expression '{$regularExpression}' given.");
		}
		
		return new FormFieldValidator(
			'format',
			function(IFormField $formField) use ($regex, $languageItemPrefix) {
				if (!$regex->match($formField->getSaveValue())) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'format',
							$languageItemPrefix . '.error.format'
						)
					);
				}
			}
		);
	}
	
	/**
	 * Disallow creating `FormFieldValidatorUtil` objects.
	 */
	private function __construct() {
		// does nothing
	}
}

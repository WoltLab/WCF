<?php
namespace wcf\system\form\builder\field\validation;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\TextFormField;
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
	 * Returns a form field validator to ensure that the value of the form field
	 * is a dot-separated string.
	 * 
	 * @param	string		$languageItemPrefix		language item prefix used for error language items `{$languageItemPrefix}.error.{errorType}`
	 * @param	int		$minimumSegmentCount		minimum number of dot-separated segments, or `-1` if there is no minimum
	 * @param	int		$maximumSegmentCount		maximum number of dot-separated segments, or `-1` if there is no maximum
	 * @param	string		$segmentRegularExpression	regular expression used to validate each segment
	 * @return	FormFieldValidator
	 */
	public static function getDotSeparatedStringValidator($languageItemPrefix, $minimumSegmentCount = 3, $maximumSegmentCount = -1, $segmentRegularExpression = '^[A-z0-9\-\_]+$') {
		$regex = Regex::compile($segmentRegularExpression);
		if (!$regex->isValid()) {
			throw new \InvalidArgumentException("Invalid regular expression '{$segmentRegularExpression}' given.");
		}
		
		return new FormFieldValidator('format', function(TextFormField $formField) use ($languageItemPrefix, $minimumSegmentCount, $maximumSegmentCount, $regex) {
			if ($formField->getValue()) {
				$segments = explode('.', $formField->getValue());
				if ($minimumSegmentCount !== -1 && count($segments) < $minimumSegmentCount) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'tooFewSegments',
							$languageItemPrefix . '.error.tooFewSegments',
							['segmentCount' => count($segments)]
						)
					);
				}
				else if ($maximumSegmentCount !== -1 && count($segments) > $maximumSegmentCount) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'tooManySegments',
							$languageItemPrefix . '.error.tooManySegments',
							['segmentCount' => count($segments)]
						)
					);
				}
				else {
					$invalidSegments = [];
					foreach ($segments as $key => $segment) {
						if (!$regex->match($segment)) {
							$invalidSegments[$key] = $segment;
						}
					}
					
					if (!empty($invalidSegments)) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'invalidSegments',
								'wcf.form.fieldValidator.dotSeparatedString.error.invalidSegments',
								['invalidSegments' => $invalidSegments]
							)
						);
					}
				}
			}
		});
	}
	
	/**
	 * Returns a form field validator to check the form field value against
	 * the given regular expression. The regex is not checked if the form
	 * field is empty and not required. 
	 * 
	 * @param	string		$regularExpression	regular expression used to validate form field value
	 * @param	string		$languageItemPrefix	language item prefix used for error language item `{$languageItemPrefix}.error.format`
	 *
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
				$value = $formField->getSaveValue();
				
				// ignore empty non-required form fields
				if ($value === '' && !$formField->isRequired()) {
					return;
				}
				
				if (!$regex->match($value)) {
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

<?php
namespace wcf\system\form\builder\field;
use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Provides default implementations of `IMinimumLengthFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
trait TMinimumLengthFormField {
	/**
	 * minimum length of the field value
	 * @var	null|int
	 */
	protected $minimumLength;
	
	/**
	 * Returns the minimum length of the values of this field or `null` if no placeholder
	 * has been set.
	 * 
	 * @return	null|int
	 */
	public function getMinimumLength() {
		return $this->minimumLength;
	}
	
	/**
	 * Sets the minimum length of the values of this field. If `null` is passed, the
	 * minimum length is removed.
	 * 
	 * @param	null|int	$minimumLength	minimum field value length
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given minimum length is no integer or otherwise invalid
	 */
	public function minimumLength($minimumLength = null) {
		if ($minimumLength !== null) {
			if (!is_int($minimumLength)) {
				throw new \InvalidArgumentException("Given minimum length is no int, '" . gettype($minimumLength) . "' given.");
			}
			
			if ($minimumLength < 0) {
				throw new \InvalidArgumentException("Minimum length must be non-negative, '{$minimumLength}' given.");
			}
			
			if ($this instanceof IMaximumLengthFormField) {
				$maximumLength = $this->getMaximumLength();
				if ($maximumLength !== null && $minimumLength > $maximumLength) {
					throw new \InvalidArgumentException("Minimum length ({$minimumLength}) cannot be greater than maximum length ({$maximumLength}).");
				}
			}
		}
		
		$this->minimumLength = $minimumLength;
		
		return $this;
	}
	
	/**
	 * Validates the minimum length of the given text.
	 * 
	 * @param	string		$text		validated text
	 * @param	null|Language	$language	language of the validated text
	 */
	public function validateMinimumLength($text, Language $language = null) {
		if ($this->getMinimumLength() !== null && mb_strlen($text) < $this->getMinimumLength()) {
			$this->addValidationError(new FormFieldValidationError(
				'minimumLength',
				'wcf.form.field.text.error.minimumLength',
				[
					'language' => $language,
					'length' => mb_strlen($text),
					'minimumLength' => $this->getMinimumLength()
				]
			));
		}
	}
}

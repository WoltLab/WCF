<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for to select a FontAwesome icon.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class DateFormField extends AbstractFormField implements IImmutableFormField {
	use TImmutableFormField;
	
	/**
	 * date time format of the save value
	 * @var	string
	 */
	protected $__saveValueFormat = null;
	
	/**
	 * is `true` if not only the date, but also the time can be set
	 * @var	bool
	 */
	protected $__supportsTime = false;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__dateFormField';
	
	/**
	 * Returns the type of the returned save value.
	 * 
	 * If no save value format has been set, `U` (unix timestamp) will be set and returned.
	 * 
	 * @return	string
	 */
	public function getSaveValueFormat() {
		if ($this->__saveValueFormat === null) {
			$this->__saveValueFormat = 'U';
		}
		
		return $this->__saveValueFormat;
	}
	
	/**
	 * Returns a date time object for the current value or `null` if no date time
	 * object could be created.
	 * 
	 * @return	\DateTime|null
	 */
	protected function getValueDateTimeObject() {
		if ($this->supportsTime()) {
			$dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->getValue());
		}
		else {
			$dateTime = \DateTime::createFromFormat('Y-m-d', $this->getValue());
		}
		
		if ($dateTime === false) {
			return null;
		}
		
		return $dateTime;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->getValue() === null) {
			return null;
		}
		
		return $this->getValueDateTimeObject()->format($this->getSaveValueFormat());
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId()) && is_string($this->getDocument()->getRequestData($this->getPrefixedId()))) {
			$this->__value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if ($this->__value === '') {
				$this->__value = null;
			}
		}
		
		return $this;
	}
	
	/**
	 * Sets the date time format of the save value.
	 * 
	 * @param	string		$saveValueFormat
	 * @return	static
	 */
	public function saveValueFormat($saveValueFormat) {
		if ($this->__saveValueFormat !== null) {
			throw new \BadMethodCallException("Save value type has already been set.");
		}
		
		try {
			\DateTime::createFromFormat($saveValueFormat, TIME_NOW);
		}
		catch (\Exception $e) {
			throw new \InvalidArgumentException("Invalid date time format '{$saveValueFormat}'.", 0, $e);
		}
		
		$this->__saveValueFormat = $saveValueFormat;
		
		return $this;
	}
	
	/**
	 * Sets if not only the date, but also the time can be set.
	 *
	 * @param	bool		$supportsTime
	 * @return	static		thsi field
	 */
	public function supportTime($supportsTime = true) {
		$this->__supportsTime = $supportsTime;
		
		return $this;
	}
	
	/**
	 * Returns `true` if not only the date, but also the time can be set, and
	 * returns `false` otherwise.
	 * 
	 * @return	bool
	 */
	public function supportsTime() {
		return $this->__supportsTime;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->getValue() === null) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		else {
			if ($this->getValueDateTimeObject() === null) {
				$this->addValidationError(new FormFieldValidationError(
					'format',
					'wcf.form.field.date.error.format'
				));
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		parent::value($value);
		
		$dateTime = \DateTime::createFromFormat($this->getSaveValueFormat(), $this->getValue());
		if ($dateTime === false) {
			throw new \InvalidArgumentException("Given value does not match format '{$this->getSaveValueFormat()}'.");
		}
		
		if ($this->supportsTime()) {
			parent::value($dateTime->format('Y-m-d\TH:i:sP'));
		}
		else {
			parent::value($dateTime->format('Y-m-d'));
		}
		
		return $this;
	}
}

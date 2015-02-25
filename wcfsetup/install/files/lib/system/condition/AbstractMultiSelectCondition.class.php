<?php
namespace wcf\system\condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Abstract implementation of a condition with multi select options.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractMultiSelectCondition extends AbstractSelectCondition {
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::$fieldValue
	 */
	protected $fieldValue = array();
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if (!empty($this->fieldValue)) {
			return array(
				$this->fieldName => $this->fieldValue
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::getFieldElement()
	 */
	protected function getFieldElement() {
		$options = $this->getOptions();
		
		$fieldElement = '<select name="'.$this->fieldName.'[]" id="'.$this->fieldName.'" multiple="multiple" size="'.(count($options, COUNT_RECURSIVE) > 10 ? 10 : count($options)).'">';
		foreach ($options as $key => $value) {
			if (is_array($value)) {
				$fieldElement .= $this->getOptGroupCode($key, $value);
			}
			else {
				$fieldElement .= $this->getOptionCode($key, $value);
			}
		}
		$fieldElement .= "</select>";
		
		return $fieldElement;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptionCode()
	 */
	protected function getOptionCode($value, $label) {
		return '<option value="'.$value.'"'.(in_array($value, $this->fieldValue) ? ' selected="selected"' : '').'>'.WCF::getLanguage()->get($label).'</option>';
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->fieldName]) && is_array($_POST[$this->fieldName])) $this->fieldValue = ArrayUtil::toIntegerArray($_POST[$this->fieldName]);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function reset() {
		$this->fieldValue = array();
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		$options = $this->getOptions();
		foreach ($this->fieldValue as $value) {
			if (!isset($options[$value])) {
				foreach ($options as $optionValue) {
					if (is_array($optionValue) && isset($optionValue[$value])) {
						return;
					}
				}
				
				$this->errorMessage = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException($this->fieldName, 'noValidSelection');
			}
		}
	}
}

<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Abstract implementation of a condition with select options.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractSelectCondition extends AbstractSingleFieldCondition {
	/**
	 * name of the field
	 * @var	string
	 */
	protected $fieldName = '';
	
	/**
	 * value of the selected option
	 * @var	string
	 */
	protected $fieldValue = self::NO_SELECTION_VALUE;
	
	/**
	 * value of the "no selection" option
	 * @var	string
	 */
	const NO_SELECTION_VALUE = -1;
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if ($this->fieldValue != self::NO_SELECTION_VALUE) {
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
		
		$fieldElement = '<select name="'.$this->fieldName.'">';
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
	 * Returns the html code for an opt group.
	 * 
	 * @param	string			$label
	 * @param	array<string>		$options
	 * @return	string
	 */
	protected function getOptGroupCode($label, array $options) {
		$html = '<optgroup label="'.$label.'">';
		foreach ($options as $key => $value) {
			$html .= $this->getOptionCode($key, $value);
		}
		$html .= '</optgroup>';
		
		return $html;
	}
	
	/**
	 * Returns the html code for an option.
	 * 
	 * @param	string		$value
	 * @param	string		$label
	 * @return	string
	 */
	protected function getOptionCode($value, $label) {
		return '<option value="'.$value.'"'.($this->fieldValue == $value ? ' selected="selected"' : '').'>'.WCF::getLanguage()->get($label).'</option>';
	}
	
	/**
	 * Returns the selectable options.
	 */
	abstract protected function getOptions();
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->fieldName])) $this->fieldValue = intval($_POST[$this->fieldName]);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->fieldValue = self::NO_SELECTION_VALUE;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		$this->fieldValue = $condition->conditionData[$this->fieldName];
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		if ($this->fieldValue != self::NO_SELECTION_VALUE) {
			$options = $this->getOptions();
			
			if (!isset($options[$this->fieldValue])) {
				foreach ($options as $key => $value) {
					if (is_array($value) && isset($value[$this->fieldValue])) {
						return;
					}
				}
				
				$this->errorMessage = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException($this->fieldName, 'noValidSelection');
			}
		}
	}
}

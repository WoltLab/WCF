<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a text condition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractTextCondition extends AbstractSingleFieldCondition {
	/**
	 * name of the field
	 * @var	string
	 */
	protected $fieldName = '';
	
	/**
	 * entered condition field value
	 * @var	string
	 */
	protected $fieldValue = '';
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if (mb_strlen($this->fieldValue)) {
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
		return '<input type="text" name="'.$this->fieldName.'" value="'.StringUtil::encodeHTML($this->fieldValue).'" class="long" />';
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->fieldName])) $this->fieldValue = StringUtil::trim($_POST[$this->fieldName]);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->fieldValue = '';
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		$this->fieldValue = $condition->conditionData[$this->fieldName];
	}
}

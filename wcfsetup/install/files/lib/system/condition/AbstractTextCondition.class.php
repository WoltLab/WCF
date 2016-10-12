<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a text condition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
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
	 * @inheritDoc
	 */
	public function getData() {
		if (mb_strlen($this->fieldValue)) {
			return [$this->fieldName => $this->fieldValue];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		return '<input type="text" name="'.$this->fieldName.'" value="'.StringUtil::encodeHTML($this->fieldValue).'" class="long">';
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->fieldName])) $this->fieldValue = StringUtil::trim($_POST[$this->fieldName]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->fieldValue = '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->fieldValue = $condition->conditionData[$this->fieldName];
	}
}

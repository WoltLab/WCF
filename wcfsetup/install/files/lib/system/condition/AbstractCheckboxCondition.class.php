<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a condition realized by a checkbox.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractCheckboxCondition extends AbstractSingleFieldCondition {
	/**
	 * name of the checkbox
	 * @var	string
	 */
	protected $fieldName;
	
	/**
	 * is `1` if the checkbox is checked
	 * @var	integer
	 */
	protected $fieldValue = 0;
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	public function __construct(DatabaseObject $object) {
		parent::__construct($object);
		
		if ($this->fieldName === null) {
			throw new SystemException("Field name has not been set.");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if ($this->fieldValue) {
			return [$this->fieldName => $this->fieldValue];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFieldElement() {
		return '<label><input type="checkbox" name="' . $this->fieldName . '" id="' . $this->fieldName . '"'.($this->fieldValue ? ' checked="checked"' : '').' /> '.WCF::getLanguage()->get($this->label).'</label>';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getLabel() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (!empty($_POST[$this->fieldName])) $this->fieldValue = 1;
		else $this->fieldValue = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->fieldValue = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->fieldValue = $condition->{$this->fieldName};
	}
}

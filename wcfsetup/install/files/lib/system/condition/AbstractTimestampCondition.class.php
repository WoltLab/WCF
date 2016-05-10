<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for compairing a user-bound timestamp with a fixed time
 * interval.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractTimestampCondition extends AbstractSingleFieldCondition implements IObjectCondition, IObjectListCondition {
	/**
	 * name of the relevant database object class
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * registration start date
	 * @var	string
	 */
	protected $endTime = '';
	
	/**
	 * name of the relevant object property
	 * @var	string
	 */
	protected $propertyName = '';
	
	/**
	 * registration start date
	 * @var	string
	 */
	protected $startTime = '';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		$className = $this->getListClassName();
		if (!($objectList instanceof $className)) {
			throw new \InvalidArgumentException("Object list is no instance of '{$className}', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias().'.'.$this->getPropertyName().' <> ?', [0]);
		if (isset($conditionData['endTime'])) {
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias().'.'.$this->getPropertyName().' < ?', [strtotime($conditionData['endTime']) + 86400]);
		}
		if (isset($conditionData['startTime'])) {
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias().'.'.$this->getPropertyName().' >= ?', [strtotime($conditionData['startTime'])]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		$className = $this->getClassName();
		if (!($object instanceof $className)) return;
		
		if (isset($conditionData['startTime']) && $object->{$this->getPropertyName()} < strtotime($conditionData['startTime'])) {
			return false;
		}
		if (isset($conditionData['endTimeTime']) && $object->{$this->getPropertyName()} >= strtotime($conditionData['endTime']) + 86400) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the name of the relevant database object class.
	 * 
	 * @return	string
	 */
	protected function getClassName() {
		return $this->className;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if (strlen($this->startTime)) {
			$data['startTime'] = $this->startTime;
		}
		if (strlen($this->endTime)) {
			$data['endTime'] = $this->endTime;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		$start = WCF::getLanguage()->get('wcf.date.period.start');
		$end = WCF::getLanguage()->get('wcf.date.period.end');
		
		return <<<HTML
<input type="date" id="{$this->getPropertyName()}StartTime" name="{$this->getPropertyName()}StartTime" value="{$this->startTime}" placeholder="{$start}" />
<input type="date" id="{$this->getPropertyName()}EndTime" name="{$this->getPropertyName()}EndTime" value="{$this->endTime}" placeholder="{$end}" />
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getLabel() {
		return WCF::getLanguage()->get($this->getLanguageItemPrefix().'.'.$this->getPropertyName());
	}
	
	/**
	 * Returns the prefix of the language items used for the condition.
	 * 
	 * @return	string
	 */
	abstract protected function getLanguageItemPrefix();
	
	/**
	 * Returns the name of the relevant database object list class.
	 * 
	 * @return	string
	 */
	protected function getListClassName() {
		return $this->className.'List';
	}
	
	/**
	 * Returns the name of the relevant object property.
	 * 
	 * @return	string
	 */
	protected function getPropertyName() {
		return $this->propertyName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->getPropertyName().'EndTime'])) $this->endTime = $_POST[$this->getPropertyName().'EndTime'];
		if (isset($_POST[$this->getPropertyName().'StartTime'])) $this->startTime = $_POST[$this->getPropertyName().'StartTime'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->endTime = '';
		$this->startTime = '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if ($condition->endTime) {
			$this->endTime = $condition->endTime;
		}
		if ($condition->startTime) {
			$this->startTime = $condition->startTime;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$endTime = $startTime = null;
		if (strlen($this->startTime)) {
			$startTime = @strtotime($this->startTime);
			if ($startTime === false) {
				$this->errorMessage = 'wcf.condition.timestamp.error.startNotValid';
				
				throw new UserInputException($this->getPropertyName(), 'startNotValid');
			}
		}
		if (strlen($this->endTime)) {
			$endTime = @strtotime($this->endTime);
			if ($endTime === false) {
				$this->errorMessage = 'wcf.condition.timestamp.error.endNotValid';
				
				throw new UserInputException($this->getPropertyName(), 'endNotValid');
			}
		}
		
		if ($endTime !== null && $startTime !== null && $endTime < $startTime) {
			$this->errorMessage = 'wcf.condition.timestamp.error.endBeforeStart';
			
			throw new UserInputException($this->getPropertyName(), 'endBeforeStart');
		}
	}
}

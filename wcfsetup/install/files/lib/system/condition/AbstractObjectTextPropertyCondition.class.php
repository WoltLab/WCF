<?php
namespace wcf\system\condition;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\util\ClassUtil;

/**
 * Abstract condition implementation for check a text-typed property of a database
 * object.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractObjectTextPropertyCondition extends AbstractTextCondition implements IObjectCondition, IObjectListCondition {
	/**
	 * name of the relevant database object class
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * is true if the entered value should be split by commas to search for
	 * multiple values
	 * @var	boolean
	 */
	protected $supportsMultipleValues = false;
	
	/**
	 * name of the relevant object property
	 * @var	string
	 */
	protected $propertyName = '';
	
	/**
	 * @see	\wcf\system\condition\IObjectListCondition::addObjectListCondition()
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!ClassUtil::isInstanceOf($objectList, $this->getListClassName())) return;
		
		if ($this->supportsMultipleValues) {
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias().'.'.$this->getPropertyName().' IN (?)', [ $conditionData[$this->fieldName] ]);
		}
		else {
			$objectList->getConditionBuilder()->add($objectList->getDatabaseTableAlias().'.'.$this->getPropertyName().' = ?', [ $conditionData[$this->fieldName] ]);
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IObjectCondition::checkObject()
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!ClassUtil::isInstanceOf($object, $this->getClassName())) return;
		
		return in_array($object->{$this->getPropertyName()}, $conditionData[$this->fieldName]);
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
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		$value = parent::getData();
		if ($value === null || !$this->supportsMultipleValues) {
			return $value;
		}
		
		return [
			$this->fieldName => preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY)
		];
	}
	
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
}

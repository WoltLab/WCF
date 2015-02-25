<?php
namespace wcf\system\label\object\type;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Label object type container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.label.object.type
 * @category	Community Framework
 */
class LabelObjectTypeContainer implements \Countable, \Iterator {
	/**
	 * true if container represents only a boolean option
	 * @var	boolean
	 */
	public $isBooleanOption = false;
	
	/**
	 * list of object types
	 * @var	array<\wcf\system\label\object\type>
	 */
	public $objectTypes = array();
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * option value if container is a boolean option itself
	 * @var	boolean
	 */
	public $optionValue = false;
	
	/**
	 * iterator position
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * Creates a new LabelObjectTypeContainer object.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	boolean		$isBooleanOption
	 * @param	boolean		$optionValue
	 */
	public function __construct($objectTypeID, $isBooleanOption = false, $optionValue = false) {
		$this->objectTypeID = $objectTypeID;
		$this->isBooleanOption = $isBooleanOption;
		$this->optionValue = $optionValue;
	}
	
	/**
	 * Adds a label object type.
	 * 
	 * @param	\wcf\system\label\object\type\LabelObjectType	$objectType
	 */
	public function add(LabelObjectType $objectType) {
		$this->objectTypes[] = $objectType;
	}
	
	/**
	 * Returns the object type id.
	 * 
	 * @return	integer
	 */
	public function getObjectTypeID() {
		return $this->objectTypeID;
	}
	
	/**
	 * Returns the object type name.
	 * 
	 * @return	string
	 */
	public function getObjectTypeName() {
		return ObjectTypeCache::getInstance()->getObjectType($this->getObjectTypeID())->objectType;
	}
	
	/**
	 * Returns true, if container represents only a boolean option.
	 * 
	 * @return	boolean
	 */
	public function isBooleanOption() {
		return $this->isBooleanOption;
	}
	
	/**
	 * Returns option value.
	 * 
	 * @return	boolean
	 */
	public function getOptionValue() {
		return $this->optionValue;
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->objectTypes[$this->position];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->position;
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->objectTypes[$this->position]);
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->objectTypes);
	}
}

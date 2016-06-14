<?php
namespace wcf\system\label\object\type;

/**
 * Label object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Label\Object\Type
 */
class LabelObjectType {
	/**
	 * indentation level
	 * @var	integer
	 */
	public $depth = 0;
	
	/**
	 * object type is a category
	 * @var	boolean
	 */
	public $isCategory = false;
	
	/**
	 * object type label
	 * @var	string
	 */
	public $label = '';
	
	/**
	 * object id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * option value
	 * @var	integer
	 */
	public $optionValue = 0;
	
	/**
	 * Creates a new LabelObjectType object.
	 * 
	 * @param	string		$label
	 * @param	integer		$objectID
	 * @param	integer		$depth
	 * @param	boolean		$isCategory
	 */
	public function __construct($label, $objectID = 0, $depth = 0, $isCategory = false) {
		$this->depth = $depth;
		$this->isCategory = $isCategory;
		$this->label = $label;
		$this->objectID = $objectID;
	}
	
	/**
	 * Returns the label.
	 * 
	 * @return	string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * Returns the object id.
	 * @return	integer
	 */
	public function getObjectID() {
		return $this->objectID;
	}
	
	/**
	 * Returns true, if object type is a category.
	 * 
	 * @return	boolean
	 */
	public function isCategory() {
		return $this->isCategory;
	}
	
	/**
	 * Returns indentation level.
	 * 
	 * @return	integer
	 */
	public function getDepth() {
		return $this->depth;
	}
	
	/**
	 * Sets option value.
	 * 
	 * @param	integer		$optionValue
	 */
	public function setOptionValue($optionValue) {
		$this->optionValue = $optionValue;
	}
	
	/**
	 * Returns option value.
	 * 
	 * @return	integer
	 */
	public function getOptionValue() {
		return $this->optionValue;
	}
}

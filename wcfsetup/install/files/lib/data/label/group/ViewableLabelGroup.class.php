<?php
namespace wcf\data\label\group;
use wcf\data\label\Label;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ITraversableObject;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a viewable label group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 */
class ViewableLabelGroup extends DatabaseObjectDecorator implements \Countable, ITraversableObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\label\group\LabelGroup';
	
	/**
	 * list of labels
	 * @var	array<\wcf\data\label\Label>
	 */
	protected $labels = array();
	
	/**
	 * list of permissions by type
	 * @var	array<array>
	 */
	protected $permissions = array(
		'group' => array(),
		'user' => array()
	);
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of index to object relation
	 * @var	array<integer>
	 */
	protected $indexToObject = null;
	
	/**
	 * Adds a label.
	 * 
	 * @param	\wcf\data\label\Label	$label
	 */
	public function addLabel(Label $label) {
		$this->labels[$label->labelID] = $label;
		$this->indexToObject[] = $label->labelID;
	}
	
	/**
	 * Sets group permissions.
	 * 
	 * @param	array		$permissions
	 */
	public function setGroupPermissions(array $permissions) {
		$this->permissions['group'] = $permissions;
	}
	
	/**
	 * Sets user permissions.
	 * 
	 * @param	array		$permissions
	 */
	public function setUserPermissions(array $permissions) {
		$this->permissions['user'] = $permissions;
	}
	
	/**
	 * Returns true, if label is known.
	 * 
	 * @param	integer		$labelID
	 * @return	boolean
	 */
	public function isValid($labelID) {
		return isset($this->labels[$labelID]);
	}
	
	/**
	 * Returns true, if current user fulfils option id permissions.
	 * 
	 * @param	integer		$optionID
	 * @return	boolean
	 */
	public function getPermission($optionID) {
		// validate by user id
		$userID = WCF::getUser()->userID;
		if ($userID) {
			if (isset($this->permissions['user'][$userID]) && isset($this->permissions['user'][$userID][$optionID])) {
				if ($this->permissions['user'][$userID][$optionID] == 1) {
					return true;
				}
			}
		}
		
		// validate by group id
		$groupIDs = WCF::getUser()->getGroupIDs();
		foreach ($groupIDs as $groupID) {
			if (isset($this->permissions['group'][$groupID]) && isset($this->permissions['group'][$groupID][$optionID])) {
				if ($this->permissions['group'][$groupID][$optionID] == 1) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Returns a list of label ids.
	 * 
	 * @return	array<integer>
	 */
	public function getLabelIDs() {
		return array_keys($this->labels);
	}
	
	/**
	 * Returns a list of labels.
	 * 
	 * @return	array<\wcf\data\label\Label>
	 */
	public function getLabels() {
		return $this->labels;
	}
	
	/**
	 * Returns a label by id.
	 * 
	 * @param	integer		$labelID
	 * @return	\wcf\data\label\Label
	 */
	public function getLabel($labelID) {
		if (isset($this->labels[$labelID])) {
			return $this->labels[$labelID];
		}
		
		return null;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->labels);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		$objectID = $this->indexToObject[$this->index];
		return $this->labels[$objectID];
	}
	
	/**
	 * CAUTION: This methods does not return the current iterator index,
	 * rather than the object key which maps to that index.
	 * 
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->indexToObject[$this->index];
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->indexToObject[$this->index]);
	}
	
	/**
	 * @see	\SeekableIterator::seek()
	 */
	public function seek($index) {
		$this->index = $index;
		
		if (!$this->valid()) {
			throw new \OutOfBoundsException();
		}
	}
	
	/**
	 * @see	\wcf\data\ITraversableObject::seekTo()
	 */
	public function seekTo($objectID) {
		$this->index = array_search($objectID, $this->indexToObject);
		
		if ($this->index === false) {
			throw new SystemException("object id '".$objectID."' is invalid");
		}
	}
	
	/**
	 * @see	\wcf\data\ITraversableObject::search()
	 */
	public function search($objectID) {
		try {
			$this->seekTo($objectID);
			return $this->current();
		}
		catch (SystemException $e) {
			return null;
		}
	}
}

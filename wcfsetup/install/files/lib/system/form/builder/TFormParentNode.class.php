<?php
namespace wcf\system\form\builder;
use wcf\system\form\builder\field\IFormField;

/**
 * Provides default implementations of `IFormParentNode` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 * 
 * @mixin	IFormParentNode
 */
trait TFormParentNode {
	/**
	 * child nodes of this node
	 * @var	IFormChildNode[]
	 */
	protected $__children = [];
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * Appends the given node to this node and returns this node.
	 * 
	 * @param	IFormChildNode		$child		appended child
	 * @return	static					this node
	 * 
	 * @throws	\InvalidArgumentException		if the given child node cannot be appended
	 */
	public function appendChild(IFormChildNode $child) {
		$this->validateChild($child);
		
		$this->__children[] = $child;
		
		$child->parent($this);
		
		return $this;
	}
	
	/**
	 * Appends the given children to this node and returns this node.
	 * 
	 * @param	IFormChildNode[]	$children	appended children
	 * @return	static					this node
	 * 
	 * @throws	\InvalidArgumentException		if any of the given child nodes is invalid or cannot be appended
	 */
	public function appendChildren(array $children) {
		foreach ($children as $child) {
			$this->appendChild($child);
		}
		
		return $this;
	}
	
	/**
	 * Returns `true` if this node (or any of the child nodes) contains the node
	 * with the given id and returns `false` otherwise.
	 * 
	 * @param	string		$nodeId		id of searched node
	 * @return	bool
	 */
	public function contains($nodeId) {
		static::validateId($nodeId);
		
		foreach ($this->children() as $child) {
			if ($child->getId() === $nodeId) {
				return true;
			}
			
			if ($child instanceof IFormParentNode && $child->contains($nodeId) === true) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns all child nodes of this node.
	 * 
	 * @return	IFormChildNode[]	children of this node
	 */
	public function children() {
		return $this->__children;
	}
	
	/**
	 * Returns the number of direct children of this node.
	 * 
	 * @return	int	number of children
	 */
	public function count() {
		return count($this->__children);
	}
	
	/**
	 * Returns the current child node during the iteration.
	 * 
	 * @return	IFormChildNode		current child node
	 */
	public function current() {
		return $this->__children[$this->index];
	}
	
	/**
	 * Returns an iterator for the current child node.
	 * 
	 * @return	null|IFormParentNode		iterator for the current child node
	 */
	public function getChildren() {
		$node = $this->__children[$this->index];
		if ($node instanceof IFormParentNode) {
			return $node;
		}
		
		// signal leafs to \RecursiveIteratorIterator so that leaves do no have to
		// implement \RecursiveIterator; exception will be ignored because of the
		// constructor flag `\RecursiveIteratorIterator::CATCH_GET_CHILD`
		throw new \BadMethodCallException();
	}
	
	/**
	 * Returns a recursive iterator for this node. 
	 * 
	 * @return	\RecursiveIteratorIterator	recursive iterator for this node
	 */
	public function getIterator() {
		return new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD);
	}
	
	/**
	 * Returns the node with the given id or `null` if no such node exists.
	 * 
	 * All descendants, not only the direct child nodes, are checked to find the
	 * requested node.
	 * 
	 * @param	string		$nodeId		id of the requested node
	 * @return	null|IFormNode			requested node
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function getNodeById($nodeId) {
		static::validateId($nodeId);
		
		foreach ($this->children() as $child) {
			if ($child->getId() === $nodeId) {
				return $child;
			}
			
			if ($child instanceof IFormParentNode) {
				$node = $child->getNodeById($nodeId);
				if ($node !== null) {
					return $node;
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Returns `true` if the node as any children and return `false` otherwise.
	 * 
	 * @return	bool
	 */
	public function hasChildren() {
		return !empty($this->__children);
	}
	
	/**
	 * Returns `true` if this node or any of its children has a validation error and
	 * return `false` otherwise.
	 *
	 * @return	bool
	 */
	public function hasValidationErrors() {
		foreach ($this->children() as $child) {
			if ($child instanceof IFormField) {
				if (!empty($child->getValidationErrors())) {
					return true;
				}
			}
			else if ($child instanceof IFormParentNode) {
				if ($child->hasValidationErrors()) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Inserts the given node before the node with the given id and returns this node.
	 *
	 * @param	IFormChildNode		$child			inserted child node
	 * @param	string			$referenceNodeId	id of the node before which the given node is inserted
	 * @return	static						this node
	 *
	 * @throws	\InvalidArgumentException			if given node cannot be inserted or reference node id is invalid
	 */
	public function insertBefore(IFormChildNode $child, $referenceNodeId) {
		$didInsertNode = false;
		foreach ($this->children() as $index => $existingChild) {
			if ($existingChild->getId() === $referenceNodeId) {
				array_splice($this->__children, $index, 0, [$child]);
				
				$child->parent($this);
				
				$didInsertNode = true;
				break;
			}
		}
		
		if (!$didInsertNode) {
			throw new \InvalidArgumentException("Unknown child node with id '{$referenceNodeId}'.");
		}
		
		return $this;
	}
	
	/**
	 * Return the key of the current element during the iteration.
	 * 
	 * @return	int	element key during the iteration
	 */
	public function key() {
		return $this->index;
	}
	
	/**
	 * Moves the iterator internally forward to next element.
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * Reads the value of this node and its children from request data and
	 * return this field.
	 * 
	 * @return	IFormParentNode		this node
	 */
	public function readValues() {
		if ($this->isAvailable()) {
			foreach ($this->children() as $child) {
				if ($child instanceof IFormParentNode) {
					$child->readValues();
				}
				else if ($child instanceof IFormField && $child->isAvailable() && !$child->isImmutable()) {
					$child->readValue();
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Rewind the iterator to the first element.
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * Returns `true` if the current position during the iteration is valid and returns
	 * `false` otherwise.
	 * 
	 * @return	bool
	 */
	public function valid() {
		return isset($this->__children[$this->index]);
	}
	
	/**
	 * Validates the node.
	 * 
	 * Note: A `IFormParentNode` object may only return `true` if all of its child
	 * nodes are valid. A `IFormField` object is valid if its value is valid.
	 */
	public function validate() {
		if ($this->isAvailable() && $this->checkDependencies()) {
			foreach ($this->children() as $child) {
				// call `checkDependencies()` on form fields here so that their validate
				// method does not have to do it
				if ($child instanceof IFormField && (!$child->isAvailable() || !$child->checkDependencies())) {
					continue;
				}
				
				$child->validate();
				
				if ($child instanceof IFormField && empty($child->getValidationErrors())) {
					foreach ($child->getValidators() as $validator) {
						$validator($child);
						
						if (!empty($child->getValidationErrors())) {
							break;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Checks if the given node can be added as a child to this node.
	 * 
	 * @param	IFormChildNode		$child		validated child node
	 * 
	 * @throws	\InvalidArgumentException		if given node cannot be added as a child
	 */
	public function validateChild(IFormChildNode $child) {
		// check if a node with same id as the given node already exists
		if ($this->contains($child->getId())) {
			throw new \InvalidArgumentException("Cannot append node '{$child->getId()}' to node '{$this->getId()}' because a node with id '{$child->getId()}' already exists.");
		}
		
		// check all child nodes of the given node for duplicate node ids
		if ($child instanceof IFormParentNode) {
			/** @var IFormNode $thisChild */
			foreach ($child->getIterator() as $grandChild) {
				if ($grandChild instanceof IFormParentNode && $this->contains($grandChild->getId())) {
					throw new \InvalidArgumentException("Cannot append node '{$child->getId()}' to node '{$this->getId()}' because '{$child->getId()}' contains a node with id '{$grandChild->getId()}' that is already used by another node.");
				}
			}
		}
	}
}

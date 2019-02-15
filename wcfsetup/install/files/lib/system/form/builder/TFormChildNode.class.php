<?php
namespace wcf\system\form\builder;

/**
 * Provides default implementations of `IFormChildNode` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
trait TFormChildNode {
	/**
	 * parent node of this node
	 * @var	IFormParentNode
	 */
	protected $__parent;
	
	/**
	 * Returns the form document this node belongs to.
	 * 
	 * @return	IFormDocument			form document node belongs to
	 * 
	 * @throws	\BadMethodCallException		if form document is inaccessible for this node
	 */
	public function getDocument() {
		$element = $this;
		while ($element instanceof IFormChildNode) {
			$element = $element->getParent();
		}
		
		if ((!$element instanceof IFormDocument)) {
			throw new \BadMethodCallException("Form document is inaccessible from node '{$this->getId()}'.");
		}
		
		return $element;
	}
	
	/**
	 * Returns the parent node of this node.
	 * 
	 * @return	IFormParentNode			parent node of this node
	 * 
	 * @throws	\BadMethodCallException		if the parent node has not been set previously
	 */
	public function getParent() {
		if ($this->__parent === null) {
			throw new \BadMethodCallException("Before getting the parent node of '{$this->getId()}', it must be set.");
		}
		
		return $this->__parent;
	}
	
	/**
	 * Sets the parent node of this node and returns this node.
	 * 
	 * @param	IFormParentNode		$parentNode	new parent node of this node
	 * @return	static					this node
	 */
	public function parent(IFormParentNode $parentNode) {
		if ($this->__parent !== null) {
			throw new \BadMethodCallException("The parent node of '{$this->getId()}' has already been set.");
		}
		
		$this->__parent = $parentNode;
		
		return $this;
	}
}

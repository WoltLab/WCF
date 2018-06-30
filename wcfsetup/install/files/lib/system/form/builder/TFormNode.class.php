<?php
declare(strict_types=1);
namespace wcf\system\form\builder;
use wcf\system\form\builder\field\dependency\IFormFieldDependency;

/**
 * Provides default implementations of `IFormNode` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
trait TFormNode {
	/**
	 * additional attributes of this node
	 * @var	array
	 */
	protected $__attributes = [];
	
	/**
	 * `true` if this node is available and `false` otherwise
	 * @var	bool
	 */
	protected $__available = true;
	
	/**
	 * CSS classes of this node
	 * @var	string[]
	 */
	protected $__classes = [];
	
	/**
	 * id of the form node
	 * @var	string
	 */
	protected $__id;
	
	/**
	 * dependencies of this node
	 * @var	IFormFieldDependency[]
	 */
	protected $dependencies = [];
	
	/**
	 * is `true` if node has already been populated and is `false` otherwise 
	 * @var	bool
	 */
	protected $isPopulated = false;
	
	/**
	 * list of attribute names that may not be set using `attribute()`
	 * @var	string[]
	 */
	protected $reservedAttributes = ['class', 'id', 'name'];
	
	/**
	 * Adds the given CSS class to this node and returns this node.
	 * 
	 * @param	string		$class		added CSS class name
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function addClass(string $class): IFormNode {
		static::validateClass($class);
		
		if (!in_array($class, $this->__classes)) {
			$this->__classes[] = $class;
		}
		
		return $this;
	}
	
	/**
	 * Adds a dependency on the value of a `IFormField` so that this node is
	 * only available if the field satisfies the given dependency and returns
	 * this node.
	 * 
	 * This method is expected to set the dependent node of the given dependency
	 * to this node.
	 * 
	 * @param	IFormFieldDependency		$dependency	added node dependency
	 * @return	static					this node
	 */
	public function addDependency(IFormFieldDependency $dependency): IFormNode {
		$this->dependencies[] = $dependency;
		
		$dependency->dependentNode($this);
		
		return $this;
	}
	
	/**
	 * Adds an additional attribute to this node and returns this node.
	 *
	 * The value of an existing attribute is overwritten by the new value.
	 * 
	 * @param	string		$name		attribute name
	 * @param	null|string	$value		attribute value
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if an invalid name or value is given (some attribute names are invalid as there are specific methods for setting that attribute)
	 */
	public function attribute(string $name, string $value = null): IFormNode {
		static::validateAttribute($name);
		
		if ($value !== null && !is_bool($value) && !is_numeric($value) && !is_string($value)) {
			throw new \InvalidArgumentException("Value argument is of invalid type, " . gettype($value) . ".");
		}
		
		$this->__attributes[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Sets if this node is available and returns this node.
	 *
	 * By default, every node is available. This methods makes it easier to create forms
	 * that contains node that are only avaiable if certain options have specific values
	 * or the active user has specific permissions, for example. Furthermore, fields
	 * themselves are also able to mark themselves as unavailable, for example, a selection
	 * field without any options. A `IFormContainer` is automatically unavailable if it
	 * contains no available children.
	 *
	 * Unavailable fields produce no output, their value is not read, they are not validated
	 * and they are not checked for save values.
	 * 
	 * Note: Form field dependencies manage dynamic availability of form nodes based on
	 * form field values while this method manages static availability that is independent
	 * of form field values and only depends on external factors.
	 * 
	 * @param	bool		$available	determines if node is available
	 * @return	static				this node
	 */
	public function available(bool $available = true): IFormNode {
		$this->__available = $available;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the node's dependencies are met and returns `false` otherwise.
	 * 
	 * @return	bool
	 */
	public function checkDependencies(): bool {
		if (!empty($this->dependencies)) {
			foreach ($this->dependencies as $dependency) {
				if (!$dependency->checkDependency()) {
					return false;
				}
			}
		}
		
		if ($this instanceof IFormParentNode) {
			if (count($this) > 0) {
				/** @var IFormChildNode $child */
				foreach ($this as $child) {
					if ($child->checkDependencies()) {
						return true;
					}
				}
				
				return false;
			}
			
			// container with no children
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the value of the additional attribute of this node with the given name.
	 * 
	 * @param	string		$name		attribute name
	 * @return	mixed				attribute value
	 * 
	 * @throws	\InvalidArgumentException	if the given name is invalid or no such attribute exists
	 */
	public function getAttribute(string $name) {
		if (!$this->hasAttribute($name)) {
			throw new \InvalidArgumentException("Unknown attribute '{$name}' requested.");
		}
		
		return $this->__attributes[$name];
	}
	
	/**
	 * Returns additional attributes of this node.
	 * 
	 * @return	array		additional node attributes
	 */
	public function getAttributes(): array {
		return $this->__attributes;
	}
	
	/**
	 * Returns all CSS classes of this node.
	 * 
	 * @return	string[]	CSS classes of node
	 */
	public function getClasses(): array {
		return $this->__classes;
	}
	
	/**
	 * Returns all of the node's dependencies.
	 * 
	 * @return	IFormFieldDependency[]		node's dependencies
	 */
	public function getDependencies(): array {
		return $this->dependencies;
	}
	
	/**
	 * Returns the form document this node belongs to.
	 *
	 * @return	IFormDocument			form document node belongs to
	 *
	 * @throws	\BadMethodCallException		if form document is inaccessible for this node
	 */
	abstract public function getDocument(): IFormDocument;
	
	/**
	 * Returns additional template variables used to generate the html representation
	 * of this node.
	 *
	 * @return	array		additional template variables
	 */
	public function getHtmlVariables(): array {
		return [];
	}
	
	/**
	 * Returns the id of the form node.
	 *
	 * @return	string		node id
	 *
	 * @throws	\BadMethodCallException		if no id has been set
	 */
	public function getId(): string {
		if ($this->__id === null) {
			throw new \BadMethodCallException("Id has not been set.");
		}
		
		return $this->__id;
	}
	
	/**
	 * Returns the prefixed id of this node that means a combination of the form
	 * documents global prefix and this nodes ids.
	 *
	 * The prefixed id is primarily intended to be used when outputting the form's
	 * fields and reading their values.
	 *
	 * @return	string				prefixed node id
	 *
	 * @throws	\BadMethodCallException		if no id has been set or if form document is inaccessible for this node
	 */
	public function getPrefixedId(): string {
		return $this->getDocument()->getPrefix() . $this->getId();
	}
	
	/**
	 * Returns `true` if an additional attribute with the given name exists and returns
	 * `false` otherwise.
	 * 
	 * @param	string		$name		attribute name
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is invalid
	 */
	public function hasAttribute(string $name): bool {
		static::validateAttribute($name);
		
		return isset($this->__attributes[$name]);
	}
	
	/**
	 * Returns `true` if a CSS class with the given name exists and returns `false` otherwise.
	 *
	 * @param	string		$class		checked CSS class
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function hasClass(string $class): bool {
		static::validateClass($class);
		
		return array_search($class, $this->__classes) !== false;
	}
	
	/**
	 * Returns `true` if this node has a dependency with the given id and
	 * returns `false` otherwise.
	 * 
	 * @param	string		$dependencyId	id of the checked dependency
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function hasDependency(string $dependencyId): bool {
		foreach ($this->dependencies as $dependency) {
			if ($dependency->getId() === $dependencyId) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Sets the id of the node.
	 *
	 * @param	string		$id	new id of node
	 * @return	static			this node
	 * 
	 * @throws	\BadMethodCallException		if id has already been set
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function id(string $id): IFormNode {
		static::validateId($id);
		
		if ($this->__id !== null) {
			throw new \BadMethodCallException("Id has already been set.");
		}
		
		$this->__id = $id;
		
		return $this;
	}
	
	/**
	 * Returns `true` if this node is available and returns `false` otherwise.
	 * 
	 * If the node's own availability has not been explicitly set, it is assumed to be `true`.
	 * 
	 * @return	bool
	 * 
	 * @see		IFormNode::available()
	 */
	public function isAvailable(): bool {
		if ($this->__available && $this instanceof IFormParentNode) {
			/** @var IFormChildNode $child */
			foreach ($this as $child) {
				if ($child->isAvailable()) {
					return true;
				}
			}
			
			return false;
		}
		
		return $this->__available;
	}
	
	/**
	 * Is called once after all nodes have been added to the document this node belongs to.
	 * 
	 * This method enables this node to perform actions that require the whole document having
	 * finished constructing itself and every parent-child relationship being established.
	 * 
	 * @return	static				this node
	 * 
	 * @throws	\BadMethodCallException		if this node has already been populated
	 */
	public function populate(): IFormNode {
		if ($this->isPopulated) {
			throw new \BadMethodCallException('Node has already been populated');
		}
		
		$this->isPopulated = true;
		
		return $this;
	}
	
	
	/**
	 * Removes the given CSS class and returns this node.
	 * 
	 * If this node does not have the given CSS class, this method silently
	 * ignores that fact.
	 * 
	 * @param	string		$class		removed CSS class
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function removeClass(string $class): IFormNode {
		static::validateClass($class);
		
		$index = array_search($class, $this->__classes);
		if ($index !== false) {
			unset($this->__classes[$index]);
		}
		
		return $this;
	}
	
	/**
	 * Removes the dependency with the given id and returns this node.
	 * 
	 * @param	string		$dependencyId	id of the removed dependency
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid or no such dependency exists
	 */
	public function removeDependency(string $dependencyId): IFormNode {
		foreach ($this->dependencies as $key => $dependency) {
			if ($dependency->getId() === $dependencyId) {
				unset($this->dependencies[$key]);
				
				return $this;
			}
		}
		
		throw new \InvalidArgumentException("Unknown dependency with id '{$dependencyId}'.");
	}
	
	/**
	 * Creates a new element with the given id.
	 * 
	 * @param	string		$id	node id
	 * @return	static		this node
	 * 
	 * @throws	\InvalidArgumentException	if the given id is already used by another node, or otherwise is invalid
	 */
	public static function create(string $id): IFormNode {
		return (new static)->id($id);
	}
	
	/**
	 * Checks if the given attribute name class a string and a valid attribute name.
	 * 
	 * @param	string		$name		checked argument name
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is invalid
	 */
	public static function validateAttribute(string $name) {
		if (preg_match('~^[_A-z][_A-z0-9-]*$~', $name) !== 1) {
			throw new \InvalidArgumentException("Invalid name '{$name}' given.");
		}
	}
	
	/**
	 * Checks if the given parameter class a string and a valid node class.
	 * 
	 * @param	string		$class		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public static function validateClass(string $class) {
		// regular expression is a more restrictive version of
		// (https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#w3cselgrammar)
		if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $class) !== 1) {
			throw new \InvalidArgumentException("Invalid class '{$class}' given.");
		}
	}
	
	/**
	 * Checks if the given parameter is a string and a valid node id.
	 * 
	 * @param	string		$id		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public static function validateId(string $id) {
		// regular expression is a more restrictive version of
		// https://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
		if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
	}
}

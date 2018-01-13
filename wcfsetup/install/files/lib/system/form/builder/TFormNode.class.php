<?php
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
	 * @throws	\InvalidArgumentException	if the given class is no string or otherwise invalid
	 */
	public function addClass($class) {
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
	 * @return	static						this node
	 */
	public function addDependency(IFormFieldDependency $dependency) {
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
	public function attribute($name, $value = null) {
		static::validateAttribute($name);
		
		if ($value !== null && !is_bool($value) && !is_numeric($value) && !is_string($value)) {
			throw new \InvalidArgumentException("Value argument is of invalid type, " . gettype($value) . ".");
		}
		
		$this->__attributes[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the node's dependencies are met and returns `false` otherwise.
	 *
	 * @return	bool
	 */
	public function checkDependencies() {
		foreach ($this->dependencies as $dependency) {
			if (!$dependency->checkDependency()) {
				return false;
			}
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
	public function getAttribute($name) {
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
	public function getAttributes() {
		return $this->__attributes;
	}
	
	/**
	 * Returns all CSS classes of this node.
	 * 
	 * @return	string[]	CSS classes of node
	 */
	public function getClasses() {
		return $this->__classes;
	}
	
	/**
	 * Returns all of the node's dependencies.
	 * 
	 * @return	IFormFieldDependency[]		node's dependencies
	 */
	public function getDependencies() {
		return $this->dependencies;
	}
	
	/**
	 * Returns the form document this node belongs to.
	 *
	 * @return	IFormDocument			form document node belongs to
	 *
	 * @throws	\BadMethodCallException		if form document is inaccessible for this node
	 */
	abstract public function getDocument();
	
	/**
	 * Returns additional template variables used to generate the html representation
	 * of this node.
	 *
	 * @return	array		additional template variables
	 */
	public function getHtmlVariables() {
		return [];
	}
	
	/**
	 * Returns the id of the form node.
	 *
	 * @return	string		node id
	 *
	 * @throws	\BadMethodCallException		if no id has been set
	 */
	public function getId() {
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
	public function getPrefixedId() {
		return $this->getDocument()->getPrefix() . $this->getId();
	}
	
	/**
	 * Returns `true` if an additional attribute with the given name exists and returns
	 * `false` otherwise.
	 * 
	 * @param	string		$name		attribute name
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is no string or otherwise invalid
	 */
	public function hasAttribute($name) {
		static::validateAttribute($name);
		
		return array_search($name, $this->__attributes) !== false;
	}
	
	/**
	 * Returns `true` if a CSS class with the given name exists and returns `false` otherwise.
	 *
	 * @param	string		$class		checked CSS class
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given class is no string or otherwise invalid
	 */
	public function hasClass($class) {
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
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public function hasDependency($dependencyId) {
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
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public function id($id) {
		static::validateId($id);
		
		if ($this->__id !== null) {
			throw new \BadMethodCallException("Id has already been set.");
		}
		
		$this->__id = $id;
		
		return $this;
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
	public function populate() {
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
	 * @throws	\InvalidArgumentException	if the given class is no string or otherwise invalid
	 */
	public function removeClass($class) {
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
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid or no such dependency exists
	 */
	public function removeDependency($dependencyId) {
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
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string, already used by another node, or otherwise is invalid
	 */
	public static function create($id) {
		return (new static)->id($id);
	}
	
	/**
	 * Checks if the given attribute name class a string and a valid attribute name.
	 * 
	 * @param	mixed		$name		checked argument name
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is no string or otherwise invalid
	 */
	public static function validateAttribute($name) {
		if (!is_string($name)) {
			throw new \InvalidArgumentException("Given name is no string, " . gettype($name) . " given.");
		}
		
		if (preg_match('~^[_A-z][_A-z0-9-]*$~', $name) !== 1) {
			throw new \InvalidArgumentException("Invalid name '{$name}' given.");
		}
	}
	
	/**
	 * Checks if the given parameter class a string and a valid node class.
	 * 
	 * @param	mixed		$class		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public static function validateClass($class) {
		if (!is_string($class)) {
			throw new \InvalidArgumentException("Given class is no string, " . gettype($class) . " given.");
		}
		
		// regular expression is a more restrictive version of
		// (https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#w3cselgrammar)
		if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $class) !== 1) {
			throw new \InvalidArgumentException("Invalid class '{$class}' given.");
		}
	}
	
	/**
	 * Checks if the given parameter is a string and a valid node id.
	 * 
	 * @param	mixed		$id		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public static function validateId($id) {
		if (!is_string($id)) {
			throw new \InvalidArgumentException("Given id is no string, " . gettype($id) . " given.");
		}
		
		// regular expression is a more restrictive version of
		// https://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
		if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
	}
}

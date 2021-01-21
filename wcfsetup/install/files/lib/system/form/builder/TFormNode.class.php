<?php

namespace wcf\system\form\builder;

use wcf\system\form\builder\field\dependency\IFormFieldDependency;
use wcf\system\form\builder\field\IFormField;

/**
 * Provides default implementations of `IFormNode` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder
 * @since   5.2
 *
 * @mixin   IFormNode
 */
trait TFormNode
{
    /**
     * additional attributes of this node
     * @var array
     */
    protected $attributes = [];

    /**
     * `true` if this node is available and `false` otherwise
     * @var bool
     */
    protected $available = true;

    /**
     * CSS classes of this node
     * @var string[]
     */
    protected $classes = [];

    /**
     * dependencies of this node
     * @var IFormFieldDependency[]
     */
    protected $dependencies = [];

    /**
     * id of the form node
     * @var string
     */
    protected $id;

    /**
     * is `true` if node has already been populated and is `false` otherwise
     * @var bool
     */
    protected $isPopulated = false;

    /**
     * Adds the given CSS class to this node and returns this node.
     *
     * @param   string      $class      added CSS class name
     * @return  static              this node
     *
     * @throws  \InvalidArgumentException   if the given class is invalid
     */
    public function addClass($class)
    {
        static::validateClass($class);

        if (!\in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }

        return $this;
    }

    /**
     * Adds the given CSS classes to this node and returns this node.
     *
     * @param   string[]    $classes    names added CSS classes
     * @return  static              this node
     *
     * @throws  \InvalidArgumentException   if any of the given classes is invalid
     */
    public function addClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->addClass($class);
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
     * @param   IFormFieldDependency        $dependency added node dependency
     * @return  static                      this node
     */
    public function addDependency(IFormFieldDependency $dependency)
    {
        $this->dependencies[] = $dependency;

        $dependency->dependentNode($this);

        return $this;
    }

    /**
     * Adds an additional attribute to this node and returns this node.
     *
     * The value of an existing attribute is overwritten by the new value.
     *
     * @param   string      $name       attribute name
     * @param   null|string $value      attribute value
     * @return  static              this node
     *
     * @throws  \InvalidArgumentException   if an invalid name or value is given (some attribute names are invalid as there are specific methods for setting that attribute)
     */
    public function attribute($name, $value = null)
    {
        static::validateAttribute($name);

        if ($value !== null && !\is_bool($value) && !\is_numeric($value) && !\is_string($value)) {
            throw new \InvalidArgumentException("Value argument is of invalid type, " . \gettype($value) . ".");
        }

        $this->attributes[$name] = $value;

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
     * @param   bool        $available  determines if node is available
     * @return  static              this node
     */
    public function available($available = true)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Returns `true` if the node's dependencies are met and returns `false` otherwise.
     *
     * @return  bool
     */
    public function checkDependencies()
    {
        if (!empty($this->dependencies)) {
            foreach ($this->dependencies as $dependency) {
                // check dependencies directly and check if a dependent
                // field itself is unavailable because of its dependencies
                if (!$dependency->checkDependency() || !$dependency->getField()->checkDependencies()) {
                    return false;
                }
            }
        }

        if ($this instanceof IFormParentNode) {
            if (\count($this) > 0) {
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
     * Cleans up after the whole form is not used anymore.
     * This method has to support being called multiple times.
     *
     * This method is not meant to empty the value of input fields.
     *
     * @return  static      this node
     */
    public function cleanup()
    {
        return $this;
    }

    /**
     * Returns the value of the additional attribute of this node with the given name.
     *
     * @param   string      $name       attribute name
     * @return  mixed               attribute value
     *
     * @throws  \InvalidArgumentException   if the given name is invalid or no such attribute exists
     */
    public function getAttribute($name)
    {
        if (!$this->hasAttribute($name)) {
            throw new \InvalidArgumentException("Unknown attribute '{$name}' requested.");
        }

        return $this->attributes[$name];
    }

    /**
     * Returns additional attributes of this node.
     *
     * @return  array       additional node attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns all CSS classes of this node.
     *
     * @return  string[]    CSS classes of node
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Returns all of the node's dependencies.
     *
     * @return  IFormFieldDependency[]      node's dependencies
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Returns the form document this node belongs to.
     *
     * @return  IFormDocument           form document node belongs to
     *
     * @throws  \BadMethodCallException     if form document is inaccessible for this node
     */
    abstract public function getDocument();

    /**
     * Returns additional template variables used to generate the html representation
     * of this node.
     *
     * @return  array       additional template variables
     */
    public function getHtmlVariables()
    {
        return [];
    }

    /**
     * Returns the id of the form node.
     *
     * @return  string      node id
     *
     * @throws  \BadMethodCallException     if no id has been set
     */
    public function getId()
    {
        if ($this->id === null) {
            throw new \BadMethodCallException("Id has not been set.");
        }

        return $this->id;
    }

    /**
     * Returns the prefixed id of this node that means a combination of the form
     * documents global prefix and this nodes ids.
     *
     * The prefixed id is primarily intended to be used when outputting the form's
     * fields and reading their values.
     *
     * @return  string              prefixed node id
     *
     * @throws  \BadMethodCallException     if no id has been set or if form document is inaccessible for this node
     */
    public function getPrefixedId()
    {
        return $this->getDocument()->getPrefix() . $this->getId();
    }

    /**
     * Returns `true` if an additional attribute with the given name exists and returns
     * `false` otherwise.
     *
     * @param   string      $name       attribute name
     * @return  bool
     *
     * @throws  \InvalidArgumentException   if the given attribute name is invalid
     */
    public function hasAttribute($name)
    {
        static::validateAttribute($name);

        return isset($this->attributes[$name]);
    }

    /**
     * Returns `true` if a CSS class with the given name exists and returns `false` otherwise.
     *
     * @param   string      $class      checked CSS class
     * @return  bool
     *
     * @throws  \InvalidArgumentException   if the given class is invalid
     */
    public function hasClass($class)
    {
        static::validateClass($class);

        return \array_search($class, $this->classes) !== false;
    }

    /**
     * Returns `true` if this node has a dependency with the given id and
     * returns `false` otherwise.
     *
     * @param   string      $dependencyId   id of the checked dependency
     * @return  bool
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public function hasDependency($dependencyId)
    {
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
     * @param   string      $id new id of node
     * @return  static          this node
     *
     * @throws  \BadMethodCallException     if id has already been set
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public function id($id)
    {
        static::validateId($id);

        if ($this->id !== null) {
            throw new \BadMethodCallException("Id has already been set.");
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Returns `true` if this node is available and returns `false` otherwise.
     *
     * If the node's own availability has not been explicitly set, it is assumed to be `true`.
     *
     * @return  bool
     *
     * @see     IFormNode::available()
     */
    public function isAvailable()
    {
        if ($this->available && $this instanceof IFormParentNode) {
            /** @var IFormChildNode $child */
            foreach ($this as $child) {
                if ($child->isAvailable()) {
                    return true;
                }
            }

            return false;
        }

        return $this->available;
    }

    /**
     * Is called once after all nodes have been added to the document this node belongs to.
     *
     * This method enables this node to perform actions that require the whole document having
     * finished constructing itself and every parent-child relationship being established.
     *
     * @return  static              this node
     *
     * @throws  \BadMethodCallException     if this node has already been populated
     */
    public function populate()
    {
        if ($this->isPopulated) {
            throw new \BadMethodCallException('Node has already been populated');
        }

        $this->isPopulated = true;

        // add dependent fields
        foreach ($this->getDependencies() as $dependency) {
            if ($dependency->getField() === null) {
                if ($dependency->getFieldId() === null) {
                    throw new \UnexpectedValueException("Dependency '{$dependency->getId()}' for node '{$this->getId()}' has no field.");
                }

                /** @var IFormField $field */
                $field = $this->getDocument()->getNodeById($dependency->getFieldId());
                if ($field === null) {
                    throw new \UnexpectedValueException("Unknown field with id '{$dependency->getFieldId()}' for dependency '{$dependency->getId()}'.");
                }

                $dependency->field($field);
            }
        }

        return $this;
    }

    /**
     * Removes the given attribute and returns this node.
     *
     * If this node does not have the given attribute, this method silently
     * ignores that fact.
     *
     * @param   string      $name       removed attribute
     * @return  static              this node
     *
     * @throws  \InvalidArgumentException   if the given attribute is invalid
     */
    public function removeAttribute($name)
    {
        static::validateAttribute($name);

        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Removes the given CSS class and returns this node.
     *
     * If this node does not have the given CSS class, this method silently
     * ignores that fact.
     *
     * @param   string      $class      removed CSS class
     * @return  static              this node
     *
     * @throws  \InvalidArgumentException   if the given class is invalid
     */
    public function removeClass($class)
    {
        static::validateClass($class);

        $index = \array_search($class, $this->classes);
        if ($index !== false) {
            unset($this->classes[$index]);
        }

        return $this;
    }

    /**
     * Removes the dependency with the given id and returns this node.
     *
     * @param   string      $dependencyId   id of the removed dependency
     * @return  static              this field
     *
     * @throws  \InvalidArgumentException   if the given id is invalid or no such dependency exists
     */
    public function removeDependency($dependencyId)
    {
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
     * @param   string      $id node id
     * @return  static      this node
     *
     * @throws  \InvalidArgumentException   if the given id is already used by another node, or otherwise is invalid
     */
    public static function create($id)
    {
        return (new static())->id($id);
    }

    /**
     * Returns a list of attributes that are not accessible via the attribute methods.
     *
     * @return      string[]
     * @since       5.2.11
     */
    protected static function getReservedAttributes()
    {
        return [
            'class',
            'id',
            'name',
            'style',
        ];
    }

    /**
     * Checks if the given attribute name class a and a valid attribute name.
     *
     * @param   string      $name       checked argument name
     *
     * @throws  \InvalidArgumentException   if the given attribute name is invalid
     */
    public static function validateAttribute($name)
    {
        if (\preg_match('~^[_A-z][_A-z0-9-]*$~', $name) !== 1) {
            throw new \InvalidArgumentException("Invalid name '{$name}' given.");
        }

        if (\in_array(\strtolower($name), static::getReservedAttributes())) {
            throw new \InvalidArgumentException("Attribute '{$name}' is not accessible as an attribute.");
        }
    }

    /**
     * Checks if the given parameter class a and a valid node class.
     *
     * @param   string      $class      checked id
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public static function validateClass($class)
    {
        // regular expression is a more restrictive version of
        // https://www.w3.org/TR/2011/REC-css3-selectors-20110929/#w3cselgrammar
        if (\preg_match('~^-?[_A-z][_A-z0-9-]*$~', $class) !== 1) {
            throw new \InvalidArgumentException("Invalid class '{$class}' given.");
        }
    }

    /**
     * Checks if the given parameter is a and a valid node id.
     *
     * @param   string      $id     checked id
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public static function validateId($id)
    {
        // regular expression is a more restrictive version of
        // https://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
        if (\preg_match('~^-?[_A-z][_A-z0-9-]*$~', $id) !== 1) {
            throw new \InvalidArgumentException("Invalid id '{$id}' given.");
        }
    }
}

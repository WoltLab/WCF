<?php
namespace wcf\system\form\builder;

/**
 * Represents a general form node providing common methods of all nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
interface IFormNode {
	/**
	 * Adds the given CSS class to this node and returns this node.
	 * 
	 * @param	string		$class		added CSS class name
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given class is no string or otherwise invalid
	 */
	public function addClass($class);
	
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
	public function attribute($name, $value = null);
	
	/**
	 * Returns the value of the additional attribute of this node with the given name.
	 * 
	 * @param	string		$name		attribute name
	 * @return	mixed				attribute value
	 * 
	 * @throws	\InvalidArgumentException	if the given name is invalid or no such attribute exists
	 */
	public function getAttribute($name);
	
	/**
	 * Returns additional attributes of this node.
	 * 
	 * @return	array		additional node attributes
	 */
	public function getAttributes();
	
	/**
	 * Returns all CSS classes of this node.
	 * 
	 * @return	string[]	CSS classes of node
	 */
	public function getClasses();
	
	/**
	 * Returns the form document this node belongs to.
	 * 
	 * @return	IFormDocument			form document node belongs to
	 * 
	 * @throws	\BadMethodCallException		if form document is inaccessible for this node
	 */
	public function getDocument();
	
	/**
	 * Returns the html representation of this node.
	 *
	 * @return	string		html representation of node
	 */
	public function getHtml();
	
	/**
	 * Returns additional template variables used to generate the html representation
	 * of this node.
	 * 
	 * @return	array		additional template variables
	 */
	public function getHtmlVariables();
	
	/**
	 * Returns the id of the form node.
	 * 
	 * @return	string		node id
	 * 
	 * @throws	\BadMethodCallException		if no id has been set
	 */
	public function getId();
	
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
	public function getPrefixedId();
	
	/**
	 * Returns `true` if an additional attribute with the given name exists and returns
	 * `false` otherwise.
	 * 
	 * @param	string		$name		attribute name
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is no string or otherwise invalid
	 */
	public function hasAttribute($name);
	
	/**
	 * Returns `true` if a CSS class with the given name exists and returns `false` otherwise.
	 * 
	 * @param	string		$class		checked CSS class
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given class is no string or otherwise invalid
	 */
	public function hasClass($class);
	
	/**
	 * Sets the id of the node.
	 * 
	 * @param	string		$id		new id of node
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public function id($id);
	
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
	public function removeClass($class);
	
	/**
	 * Validates the node.
	 * 
	 * Note: A `IFormParentNode` object may only return `true` if all of its child
	 * nodes are valid. A `IFormField` object is valid if its value is valid.
	 */
	public function validate();
	
	/**
	 * Creates a new element with the given id.
	 * 
	 * @param	string		$id	node id
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string, already used by another element, or otherwise is invalid
	 */
	public static function create($id);
	
	/**
	 * Checks if the given attribute name class a string and a valid attribute name.
	 *
	 * @param	mixed		$name		checked argument name
	 *
	 * @throws	\InvalidArgumentException	if the given attribute name is no string or otherwise invalid
	 */
	public static function validateAttribute($name);
	
	/**
	 * Checks if the given parameter class a string and a valid node class.
	 *
	 * @param	mixed		$class		checked class
	 *
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public static function validateClass($class);
	
	/**
	 * Checks if the given parameter is a string and a valid node id.
	 * 
	 * @param	mixed		$id		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise invalid
	 */
	public static function validateId($id);
}

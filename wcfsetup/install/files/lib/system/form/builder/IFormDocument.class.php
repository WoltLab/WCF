<?php
namespace wcf\system\form\builder;
use wcf\data\IStorableObject;

/**
 * Represents a "whole" form (document).
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
interface IFormDocument extends IFormParentNode {
	/**
	 * Sets the `action` property of the HTML `form` element and returns this document. 
	 * 
	 * @param	string		$action		form action
	 * @return	static				this document
	 * 
	 * @throws	\InvalidArgumentException	if the given action is no string or otherwise is invalid
	 */
	public function action($action);
	
	/**
	 * Returns the `action` property of the HTML `form` element.
	 * 
	 * @return	string				form action
	 * 
	 * @throws	\BadMethodCallException		if no action has been set
	 */
	public function getAction();
	
	/**
	 * Returns the array passed as the `$parameters` argument of the constructor
	 * of a database object action
	 * 
	 * @return	array		data passed to database object action
	 */
	public function getData();
	
	/**
	 * Returns the data handler for this document that is used to process the
	 * field data into a parameters array for the constructor of a database
	 * object action.
	 * 
	 * Note: The data handler comes with `DefaultFormFieldDataProcessor` as its
	 * initial data processor.
	 * 
	 * @return	IFormDataHandler	form data handler
	 */
	public function getDataHandler();
	
	/**
	 * Returns the encoding type of this form. If the form contains any
	 * `IFileFormField`, `multipart/form-data` is returned, otherwise `null`
	 * is returned.
	 * 
	 * @return	null|string		form encoding type
	 */
	public function getEnctype();
	
	/**
	 * Returns the `method` property of the HTML `form` element. If no method
	 * has been set, `post` is returned.
	 * 
	 * @return	string		form method
	 */
	public function getMethod();
	
	/**
	 * Returns the global form prefix that is prepended to form elements' names and ids to
	 * avoid conflicts with other forms. If no prefix has been set, an empty string is returned.
	 * 
	 * Note: If a prefix `foo` has been set, this method returns `foo_`. 
	 * 
	 * @return	string		global form element prefix
	 */
	public function getPrefix();
	
	/**
	 * Loads the field values from the given object and returns this document.
	 * 
	 * Per default, for each field, `IFormField::loadValueFromObject()` is called.
	 * 
	 * @param	IStorableObject		$object		object used to load field values
	 * @return	static					this document
	 */
	public function loadValuesFromObject(IStorableObject $object);
	
	/**
	 * Sets the `method` property of the HTML `form` element and returns this document.
	 * 
	 * @param	string		$method		form method
	 * @return	static				this document
	 * 
	 * @throws	\InvalidArgumentException	if the given method is no string or otherwise is invalid
	 */
	public function method($method);
	
	/**
	 * Sets the global form prefix that is prepended to form elements' names and ids to
	 * avoid conflicts with other forms and returns this document.
	 * 
	 * Note: The prefix is not relevant when using the `IFormParentNode::getNodeById()`.
	 * It is only relevant when printing the form and reading the form values.
	 * 
	 * @param	string		$prefix		global form prefix
	 * @return	static				this document
	 * 
	 * @throws	\InvalidArgumentException	if the given prefix is no string or otherwise is invalid
	 */
	public function prefix($prefix);
}

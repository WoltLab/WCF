<?php
namespace wcf\system\form;

/**
 * Interface for form element containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form
 */
interface IFormElementContainer {
	/**
	 * Returns form element container description.
	 * 
	 * @return	string
	 */
	public function getDescription();
	
	/**
	 * Sets form element container description.
	 * 
	 * @param	string		$description
	 */
	public function setDescription($description);
	
	/**
	 * Returns label.
	 * 
	 * @return	string
	 */
	public function getLabel();
	
	/**
	 * Sets label.
	 * 
	 * @param	string		$label
	 */
	public function setLabel($label);
	
	/**
	 * Returns the value of child element with given name.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function getValue($key);
	
	/**
	 * Returns a list of child elements.
	 * 
	 * @return	IFormElement[]
	 */
	public function getChildren();
	
	/**
	 * Appends a new child to stack.
	 * 
	 * @param	\wcf\system\form\IFormElement		$element
	 */
	public function appendChild(IFormElement $element);
	
	/**
	 * Preprens a new child to stack.
	 * 
	 * @param	\wcf\system\form\IFormElement		$element
	 */
	public function prependChild(IFormElement $element);
	
	/**
	 * Handles a POST or GET request.
	 * 
	 * @param	array		$variables
	 */
	public function handleRequest(array $variables);
	
	/**
	 * Returns HTML-representation of current form element container.
	 * 
	 * @param	string		$formName
	 * @return	string
	 */
	public function getHTML($formName);
	
	/**
	 * Sets localized error message for named element.
	 * 
	 * @param	string		$name
	 * @param	string		$error
	 */
	public function setError($name, $error);
}

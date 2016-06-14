<?php
namespace wcf\system\form;

/**
 * Interface for form elements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form
 */
interface IFormElement {
	/**
	 * Creates a new object of type FormElement.
	 * 
	 * @param	\wcf\system\form\IFormElementContainer		$parent
	 */
	public function __construct(IFormElementContainer $parent);
	
	/**
	 * Returns form element description.
	 * 
	 * @return	string
	 */
	public function getDescription();
	
	/**
	 * Sets form element description.
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
	 * Returns element's parent container element.
	 * 
	 * @return	\wcf\system\form\IFormElementContainer
	 */
	public function getParent();
	
	/**
	 * Returns HTML-representation of current form element.
	 * 
	 * @param	string		$formName
	 * @return	string
	 */
	public function getHTML($formName);
	
	/**
	 * Sets localized error message.
	 * 
	 * @param	string		$error
	 */
	public function setError($error);
	
	/**
	 * Returns localized error message, empty if no error occured.
	 * 
	 * @return	string
	 */
	public function getError();
}

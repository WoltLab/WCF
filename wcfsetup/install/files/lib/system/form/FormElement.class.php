<?php
namespace wcf\system\form;
use wcf\system\form\FormElementContainer;

/**
 * Interface for form elements.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
interface FormElement {
	/**
	 * Creates a new object of type FormElement.
	 *
	 * @param	FormElementContainer		$parent
	 */
	public function __construct(FormElementContainer $parent);
	
	/**
	 * Returns help message.
	 *
	 * @return	string
	 */
	public function getDescription();
	
	/**
	 * Sets help message.
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
	 * @return	FormElementContainer
	 */
	public function getParent();
	
	/**
	 * Returns HTML-representation of current form element.
	 *
	 * @param	string		$formName
	 * @return	string
	 */
	public function getHTML($formName);
}

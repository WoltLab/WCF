<?php
namespace wcf\system\form\builder\field\dependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormElement;

/**
 * Represents a dependency of one field on (the value of) another field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since	3.2
 */
interface IFormFieldDependency {
	/**
	 * Returns `true` if the dependency is met, thus if the dependant form field should
	 * be considered. Otherwise, `false` is returned.
	 * 
	 * @return	bool
	 */
	public function checkDependency();
	
	/**
	 * Sets the element whose availability depends on the value of a field.
	 * 
	 * @param	IFormElement	$element	depending element
	 * @return	static				this dependency
	 */
	public function dependentElement(IFormElement $element);
	
	/**
	 * Sets the field the availability of the element dependents on.
	 * 
	 * @param	IFormField	$field		dependent field
	 * @return	static				this dependency
	 */
	public function field(IFormField $field);
	
	/**
	 * Returns the JavaScript code required to ensure this dependency in the template.
	 *
	 * @return	string		dependency JavaScript code
	 */
	public function getHtml();
	
	/**
	 * Returns the id of the dependency.
	 * 
	 * @return	string		id of the dependency 
	 */
	public function getId();
	
	/**
	 * Returns the element whose availability depends on the value of a field.
	 * 
	 * @return	IFormElement	depending element
	 */
	public function getDependentElement();
	
	/**
	 * Returns the field the availability of the element dependents on.
	 * 
	 * @return	IFormField	dependent field
	 */
	public function getField();
	
	/**
	 * Creates a new dependency with the given id.
	 * 
	 * @param	string		$id
	 * @return	static		newly created dependency
	 * 
	 * @throws	\InvalidArgumentException	if the given id is no string or otherwise is invalid
	 */
	public static function create($id);
}

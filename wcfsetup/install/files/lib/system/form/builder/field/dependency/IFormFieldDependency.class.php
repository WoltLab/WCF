<?php
namespace wcf\system\form\builder\field\dependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;

/**
 * Represents a dependency of one node on (the value of) a field.
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
	 * Sets the node whose availability depends on the value of a field.
	 * 
	 * @param	IFormNode	$node	depending node
	 * @return	static			this dependency
	 */
	public function dependentNode(IFormNode $node);
	
	/**
	 * Sets the field the availability of the node dependents on.
	 * 
	 * @param	IFormField	$field		dependent field
	 * @return	static				this dependency
	 */
	public function field(IFormField $field);
	
	/**
	 * Returns the node whose availability depends on the value of a field.
	 * 
	 * @return	IFormNode	dependent node
	 */
	public function getDependentNode();
	
	/**
	 * Returns the field the availability of the element dependents on.
	 * 
	 * @return	IFormField	field controlling element availability
	 */
	public function getField();
	
	/**
	 * Returns the JavaScript code required to ensure this dependency in the template.
	 * 
	 * @return	string		dependency JavaScript code
	 */
	public function getHtml();
	
	/**
	 * Returns the id of this dependency.
	 * 
	 * @return	string		id of the dependency 
	 */
	public function getId();
	
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

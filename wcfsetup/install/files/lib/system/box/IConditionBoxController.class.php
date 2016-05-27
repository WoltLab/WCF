<?php
namespace wcf\system\box;
use wcf\data\object\type\ObjectType;

/**
 * Interface for dynamic box controller.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
interface IConditionBoxController extends IBoxController {
	/**
	 * Returns the name of the object type definition for the box controller's condition object types.
	 * 
	 * @return	string
	 */
	public function getConditionDefinition();
	
	/**
	 * Returns the condition objects types registered for the dynamic box controller.
	 * 
	 * @return	ObjectType[]
	 */
	public function getConditionObjectTypes();
	
	/**
	 * Returns the template containing the box conditions.
	 * 
	 * @return	string
	 */
	public function getConditionsTemplate();
	
	/**
	 * Reads the box conditions.
	 */
	public function readConditions();
	
	/**
	 * Saves the conditions for the box.
	 */
	public function saveConditions();
	
	/**
	 * Validates the read conditions for the box.
	 */
	public function validateConditions();
}

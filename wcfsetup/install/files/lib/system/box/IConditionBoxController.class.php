<?php
namespace wcf\system\box;
use wcf\data\object\type\ObjectType;

/**
 * Interface for dynamic box controller.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
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
	 * Validates the read conditions for the box.
	 */
	public function validateConditions();
}

<?php
namespace wcf\system\bulk\processing;

/**
 * Every bulk processable object type has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing
 * @since	3.0
 */
interface IBulkProcessableObjectType {
	/**
	 * Returns the name of the object type definition for the bulk actions.
	 * 
	 * @return	string
	 */
	public function getActionObjectTypeDefinition();
	
	/**
	 * Returns the output for setting up the conditions for the bulk processable
	 * object.
	 * 
	 * @return	string
	 */
	public function getConditionHTML();
	
	/**
	 * Returns the name of the object type definition for the object conditions.
	 * 
	 * @return	string
	 */
	public function getConditionObjectTypeDefinition();
	
	/**
	 * Returns the name of the prefix of the language items used in the interface.
	 * 
	 * The returned prefix has not trailing dot.
	 * 
	 * @return	string
	 */
	public function getLanguageItemPrefix();
}

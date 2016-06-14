<?php
namespace wcf\system\label\object\type;

/**
 * Every label object type handler has to implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Label\Object\Type
 */
interface ILabelObjectTypeHandler {
	/**
	 * Sets object type id.
	 * 
	 * @param	integer		$objectTypeID
	 */
	public function setObjectTypeID($objectTypeID);
	
	/**
	 * Returns object type id.
	 * 
	 * @return	integer
	 */
	public function getObjectTypeID();
	
	/**
	 * Returns a label object type container.
	 * 
	 * @return	\wcf\system\label\object\type\LabelObjectTypeContainer
	 */
	public function getContainer();
	
	/**
	 * Performs save actions.
	 */
	public function save();
}

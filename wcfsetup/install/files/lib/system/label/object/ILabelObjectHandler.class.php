<?php
namespace wcf\system\label\object;

/**
 * Every label object handler has to implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.label.object
 * @category	Community Framework
 */
interface ILabelObjectHandler {
	/**
	 * Returns a list of label group ids.
	 * 
	 * @param	array		$parameters
	 * @return	array<integer>
	 */
	public function getLabelGroupIDs(array $parameters = array());
	
	/**
	 * Returns a list of label groups.
	 * 
	 * @param	array		$parameters
	 * @return	array<\wcf\data\label\group\ViewableLabelGroup>
	 */
	public function getLabelGroups(array $parameters = array());
	
	/**
	 * Returns true, if all given label ids are valid and accessible.
	 * 
	 * @param	array<integer>		$labelIDs
	 * @param	array			$optionName
	 * @return	mixed
	 */
	public function validateLabelIDs(array $labelIDs, $optionName = '', $legacyReturnValue = true);
	
	/**
	 * Assigns labels to an object.
	 * 
	 * @param	array<integer>		$labelIDs
	 * @param	integer			$objectID
	 * @param	boolean			$validatePermissions
	 * @see		\wcf\system\label\LabelHandler::setLabels()
	 */
	public function setLabels(array $labelIDs, $objectID, $validatePermissions = true);
	
	/**
	 * Removes all assigned labels.
	 * 
	 * @param	integer		$objectID
	 * @param	boolean		$validatePermissions
	 * @see		\wcf\system\label\LabelHandler::removeLabels()
	 */
	public function removeLabels($objectID, $validatePermissions = true);
	
	/**
	 * Returns a list of assigned labels.
	 * 
	 * @param	array<integer>		$objectIDs
	 * @param	boolean			$validatePermissions
	 * @return	array<array>
	 */
	public function getAssignedLabels(array $objectIDs, $validatePermissions = true);
}

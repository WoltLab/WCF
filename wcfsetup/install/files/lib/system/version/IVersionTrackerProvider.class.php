<?php
namespace wcf\system\version;
use wcf\data\IVersionTrackerObject;
use wcf\data\object\type\IObjectTypeProvider;

/**
 * Represents objects that support some of their properties to be saved.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 */
interface IVersionTrackerProvider extends IObjectTypeProvider {
	/**
	 * Returns an array containing the values that should be stored in the database.
	 * 
	 * @param       IVersionTrackerObject   $object         target object
	 * @return      mixed[]                 property to value mapping
	 */
	public function getTrackedData(IVersionTrackerObject $object);
}

<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package updates.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category 	Community Framework
 */
class PackageUpdateList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\update\PackageUpdate';
}

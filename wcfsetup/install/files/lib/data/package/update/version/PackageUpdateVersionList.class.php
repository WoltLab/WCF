<?php
namespace wcf\data\package\update\version;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package update versions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.version
 * @category	Community Framework
 */
class PackageUpdateVersionList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\update\version\PackageUpdateVersion';
}

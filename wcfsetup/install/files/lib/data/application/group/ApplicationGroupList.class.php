<?php
namespace wcf\data\application\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of application groups.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category 	Community Framework
 */
class ApplicationGroupList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\application\group\ApplicationGroup';
}
?>

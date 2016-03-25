<?php
namespace wcf\data\acp\session\virtual;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of virtual sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.virtual
 * @category	Community Framework
 */
class ACPSessionVirtualList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = ACPSessionVirtual::class;
}

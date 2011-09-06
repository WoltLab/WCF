<?php
namespace wcf\data\clipboard\item\type;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of clipboard item types.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.item.type
 * @category 	Community Framework
 */
class ClipboardItemTypeList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\clipboard\item\type\ClipboardItemType';
}

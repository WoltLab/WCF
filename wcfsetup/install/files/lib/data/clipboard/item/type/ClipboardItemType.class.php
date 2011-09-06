<?php
namespace wcf\data\clipboard\item\type;
use wcf\data\DatabaseObject;

/**
 * Represents a clipboard item type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.item.type
 * @category 	Community Framework
 */
class ClipboardItemType extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'clipboard_item_type';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'typeID';
}

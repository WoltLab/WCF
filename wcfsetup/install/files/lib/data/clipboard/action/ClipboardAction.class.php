<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObject;

/**
 * Represents a clipboard action.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.action
 * @category	Community Framework
 *
 * @property-read	integer		$actionID
 * @property-read	integer		$packageID
 * @property-read	string		$actionName
 * @property-read	string		$actionClassName
 * @property-read	integer		$showOrder
 */
class ClipboardAction extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'clipboard_action';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'actionID';
}

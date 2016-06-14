<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObject;

/**
 * Represents a clipboard action.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Clipboard\Action
 *
 * @property-read	integer		$actionID
 * @property-read	integer		$packageID
 * @property-read	string		$actionName
 * @property-read	string		$actionClassName
 * @property-read	integer		$showOrder
 */
class ClipboardAction extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'clipboard_action';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'actionID';
}

<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObject;

/**
 * Represents a clipboard action.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Clipboard\Action
 *
 * @property-read	integer		$actionID		unique id of the clipboard action
 * @property-read	integer		$packageID		id of the package which delivers the clipboard action
 * @property-read	string		$actionName		name and textual identifier of the clipboard action 
 * @property-read	string		$actionClassName	PHP class name implementing `wcf\system\clipboard\action\IClipboardAction`
 * @property-read	integer		$showOrder		position of the clipboard action in relation to the other clipboard actions
 */
class ClipboardAction extends DatabaseObject {}

<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Clipboard\Action
 * 
 * @method	ClipboardAction		current()
 * @method	ClipboardAction[]	getObjects()
 * @method	ClipboardAction|null	search($objectID)
 * @property	ClipboardAction[]	$objects
 */
class ClipboardActionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ClipboardAction::class;
}

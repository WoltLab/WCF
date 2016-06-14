<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Clipboard\Action
 * 
 * @method	ClipboardAction		getDecoratedObject()
 * @mixin	ClipboardAction
 */
class ClipboardActionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ClipboardAction::class;
}

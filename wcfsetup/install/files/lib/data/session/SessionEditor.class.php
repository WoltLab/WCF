<?php
namespace wcf\data\session;
use wcf\data\acp\session\ACPSessionEditor;

/**
 * Provides functions to edit sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session
 * 
 * @method	Session		getDecoratedObject()
 * @mixin	Session
 */
class SessionEditor extends ACPSessionEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Session::class;
}

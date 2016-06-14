<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtualEditor;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtualEditor
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session\Virtual
 * 
 * @method	SessionVirtual		getDecoratedObject()
 * @mixin	SessionVirtual
 */
class SessionVirtualEditor extends ACPSessionVirtualEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = SessionVirtual::class;
}

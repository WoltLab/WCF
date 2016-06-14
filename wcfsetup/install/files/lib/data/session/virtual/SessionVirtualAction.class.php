<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtualAction;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtualAction
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session\Virtual
 * 
 * @method	SessionVirtual		create()
 * @method	SessionVirtualEditor[]	getObjects()
 * @method	SessionVirtualEditor	getSingleObject()
 */
class SessionVirtualAction extends ACPSessionVirtualAction {
	/**
	 * @inheritDoc
	 */
	protected $className = SessionVirtualEditor::class;
}

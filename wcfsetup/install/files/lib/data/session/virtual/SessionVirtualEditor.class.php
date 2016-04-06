<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtualEditor;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtualEditor
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtualEditor extends ACPSessionVirtualEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = SessionVirtual::class;
}

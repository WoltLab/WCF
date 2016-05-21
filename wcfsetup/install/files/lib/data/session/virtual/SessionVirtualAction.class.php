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
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtualAction extends ACPSessionVirtualAction {
	/**
	 * @inheritDoc
	 */
	protected $className = SessionVirtualEditor::class;
}

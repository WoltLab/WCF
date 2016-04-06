<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtualList;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtualList
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtualList extends ACPSessionVirtualList {
	/**
	 * @inheritDoc
	 */
	public $className = SessionVirtual::class;
}

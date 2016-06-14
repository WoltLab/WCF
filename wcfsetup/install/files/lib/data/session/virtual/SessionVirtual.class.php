<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtual;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtual
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Session\Virtual
 */
class SessionVirtual extends ACPSessionVirtual {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'session_virtual';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'virtualSessionID';
}

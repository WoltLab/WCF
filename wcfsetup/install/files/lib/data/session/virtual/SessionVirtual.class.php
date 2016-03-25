<?php
namespace wcf\data\session\virtual;
use wcf\data\acp\session\virtual\ACPSessionVirtual;

/**
 * Virtual sessions for the frontend.
 * 
 * @see		\wcf\data\acp\session\virtual\ACPSessionVirtual
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 *
 * @property-read	integer		$virtualSessionID
 * @property-read	string		$sessionID
 * @property-read	string		$ipAddress
 * @property-read	string		$userAgent
 * @property-read	integer		$lastActivityTime
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

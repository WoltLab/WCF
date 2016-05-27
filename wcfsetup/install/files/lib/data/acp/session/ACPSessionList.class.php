<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category	Community Framework
 *
 * @method	ACPSession		current()
 * @method	ACPSession[]		getObjects()
 * @method	ACPSession|null		search($objectID)
 * @property	ACPSession[]		$objects
 */
class ACPSessionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPSession::class;
}

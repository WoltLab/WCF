<?php
namespace wcf\data\session;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 *
 * @method	Session		current()
 * @method	Session[]	getObjects()
 * @method	Session|null	search($objectID)
 * @property	Session[]	$objects
 */
class SessionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Session::class;
}

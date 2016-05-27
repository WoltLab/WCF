<?php
namespace wcf\data\poll;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of polls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.poll
 * @category	Community Framework
 *
 * @method	Poll		current()
 * @method	Poll[]		getObjects()
 * @method	Poll|null	search($objectID)
 * @property	Poll[]		$objects
 */
class PollList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Poll::class;
}

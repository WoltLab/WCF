<?php
namespace wcf\data\poll;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of polls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.poll
 * @category	Community Framework
 */
class PollList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\poll\Poll';
}

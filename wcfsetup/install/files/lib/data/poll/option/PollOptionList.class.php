<?php
namespace wcf\data\poll\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of poll options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.poll
 * @subpackage	data.poll.option
 * @category	Community Framework
 */
class PollOptionList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\poll\option\PollOption';
}

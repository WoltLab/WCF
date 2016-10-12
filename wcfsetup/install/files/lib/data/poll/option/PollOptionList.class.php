<?php
namespace wcf\data\poll\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of poll options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Poll\Option
 *
 * @method	PollOption		current()
 * @method	PollOption[]		getObjects()
 * @method	PollOption|null		search($objectID)
 * @property	PollOption[]		$objects
 */
class PollOptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = PollOption::class;
}

<?php
namespace wcf\data\poll\option;
use wcf\data\poll\Poll;
use wcf\data\DatabaseObject;

/**
 * Represents a poll option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Poll\Poll
 * 
 * @property-read	int		$optionID	unique id of the poll option
 * @property-read	int		$pollID		id of the poll the option belongs to
 * @property-read	string		$optionValue	text of the poll option
 * @property-read	int		$votes		number of votes for the poll option
 * @property-read	int		$showOrder	position of the poll option in relation to the other options of the poll
 */
class PollOption extends DatabaseObject {
	/**
	 * true, if option was selected by current user
	 * @var	boolean
	 */
	public $selected = false;
	
	/**
	 * Returns relative amount of votes for this option.
	 * 
	 * @param	Poll	$poll
	 * @return	int
	 */
	public function getRelativeVotes(Poll $poll) {
		if ($poll->votes) {
			return round(($this->votes / $poll->votes) * 100);
		}
		
		return 0;
	}
}

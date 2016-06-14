<?php
namespace wcf\data\poll\option;
use wcf\data\poll\Poll;
use wcf\data\DatabaseObject;

/**
 * Represents a poll option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Poll\Poll
 * 
 * @property-read	integer		$optionID
 * @property-read	integer		$pollID
 * @property-read	string		$optionValue
 * @property-read	integer		$votes
 * @property-read	integer		$showOrder
 */
class PollOption extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'poll_option';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'optionID';
	
	/**
	 * true, if option was selected by current user
	 * @var	boolean
	 */
	public $selected = false;
	
	/**
	 * Returns relative amount of votes for this option.
	 * 
	 * @param	\wcf\data\poll\Poll
	 * @return	integer
	 */
	public function getRelativeVotes(Poll $poll) {
		if ($poll->votes) {
			return round(($this->votes / $poll->votes) * 100);
		}
		
		return 0;
	}
}

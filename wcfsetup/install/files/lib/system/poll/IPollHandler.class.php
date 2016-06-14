<?php
namespace wcf\system\poll;
use wcf\data\poll\Poll;

/**
 * Provides methods to create and manage polls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Poll
 */
interface IPollHandler {
	/**
	 * Returns true if current user may start a public poll.
	 * 
	 * @return	boolean
	 */
	public function canStartPublicPoll();
	
	/**
	 * Returns true if current user may vote.
	 * 
	 * @return	boolean
	 */
	public function canVote();
	
	/**
	 * Returns related object for given poll object.
	 * 
	 * @param	\wcf\data\poll\Poll	$poll
	 */
	public function getRelatedObject(Poll $poll);
}

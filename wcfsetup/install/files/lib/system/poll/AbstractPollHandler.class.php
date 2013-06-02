<?php
namespace wcf\system\poll;
use wcf\system\SingletonFactory;

/**
 * Basic implementation for poll handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.poll
 * @subpackage	system.poll
 * @category	Community Framework
 */
abstract class AbstractPollHandler extends SingletonFactory implements IPollHandler {
	/**
	 * @see	wcf\system\poll\IPollHandler::canStartPublicPoll()
	 */
	public function canStartPublicPoll() {
		return true;
	}
	
	/**
	 * @see	wcf\system\poll\IPollHandler::canVote()
	 */
	public function canVote() {
		return true;
	}
}

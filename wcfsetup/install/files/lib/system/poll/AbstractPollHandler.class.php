<?php
namespace wcf\system\poll;
use wcf\system\SingletonFactory;

/**
 * Basic implementation for poll handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Poll
 */
abstract class AbstractPollHandler extends SingletonFactory implements IPollHandler {
	/**
	 * @inheritDoc
	 */
	public function canStartPublicPoll() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canVote() {
		return true;
	}
}

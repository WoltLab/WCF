<?php
namespace wcf\system\moderation\queue\activation;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\IModerationQueueHandler;

/**
 * Default interface for moderation queue activation handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.moderation
 * @subpackage	system.moderation.queue.activiation
 * @category	Community Framework
 */
interface IModerationQueueActivationHandler extends IModerationQueueHandler {
	/**
	 * Enables affected content.
	 * 
	 * @param	wcf\data\moderation\queue\ModerationQueue	$queue
	 */
	public function enableContent(ModerationQueue $queue);
	
	/**
	 * Returns rendered template for disabled content.
	 * 
	 * @param	wcf\data\moderation\queue\ViewableModerationQueue	$queue
	 * @return	string
	 */
	public function getDisabledContent(ViewableModerationQueue $queue);
}

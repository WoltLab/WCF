<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\IModerationQueueHandler;

/**
 * Default interface for moderation queue report handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.moderation
 * @subpackage	system.moderation.queue.report
 * @category	Community Framework
 */
interface IModerationQueueReportHandler extends IModerationQueueHandler {
	/**
	 * Returns true if current user can report given content.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canReport($objectID);
	
	/**
	 * Returns rendered template for reported content.
	 * 
	 * @param	wcf\data\moderation\queue\ViewableModerationQueue	$queue
	 * @return	string
	 */
	public function getReportedContent(ViewableModerationQueue $queue);
	
	/**
	 * Returns reported object.
	 * 
	 * @param	integer		$objectID
	 * @return	wcf\data\IUserContent
	 */
	public function getReportedObject($objectID);
}

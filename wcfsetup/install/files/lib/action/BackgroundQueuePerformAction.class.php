<?php
namespace wcf\action;
use wcf\system\background\BackgroundQueueHandler;

/**
 * Performs background queue jobs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 * @since	2.2
 */
class BackgroundQueuePerformAction extends AbstractAction {
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		header('Content-type: application/json');
		BackgroundQueueHandler::getInstance()->performNextJob();
		echo BackgroundQueueHandler::getInstance()->getRunnableCount();
		exit;
	}
}

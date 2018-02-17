<?php
namespace wcf\action;
use wcf\system\background\BackgroundQueueHandler;

/**
 * Performs background queue jobs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 * @since	3.0
 */
class BackgroundQueuePerformAction extends AbstractAction {
	/**
	 * number of jobs that will be processed per invocation
	 * @var integer
	 */
	public static $jobsPerRun = 5;
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		header('Content-type: application/json');
		for ($i = 0; $i < self::$jobsPerRun; $i++) {
			if (BackgroundQueueHandler::getInstance()->performNextJob() === false) {
				// there were no more jobs
				break;
			}
		}
		echo BackgroundQueueHandler::getInstance()->getRunnableCount();
		exit;
	}
}

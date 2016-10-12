<?php
namespace wcf\system\background\job;

/**
 * An AbstractBackgroundJob can be performed asynchronously by
 * the background queue.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Background\Job
 * @since	3.0
 */
abstract class AbstractBackgroundJob {
	/**
	 * The number of times this job can fail, before completely
	 * dequeuing it. The default is 3.
	 * 
	 * @var	int
	 */
	const MAX_FAILURES = 3;
	
	/**
	 * The number of times this job already failed.
	 * @var	int
	 */
	private $failures = 0;
	
	/**
	 * Returns the number of times this job already failed.
	 * 
	 * @return	int
	 */
	public final function getFailures() {
		return $this->failures;
	}
	
	/**
	 * Increments the fail counter.
	 */
	public final function fail() {
		$this->failures++;
	}

	/**
	 * Returns the number of seconds to wait before requeuing a failed job.
	 * 
	 * @return	int	30 minutes by default
	 */
	public function retryAfter() {
		return 30 * 60;
	}
	
	/**
	 * Performs the job. It will automatically be requeued up to MAX_FAILURES times
	 * if it fails (either throws an Exception or does not finish until the clean up
	 * cronjob comes along).
	 */
	abstract public function perform();
}

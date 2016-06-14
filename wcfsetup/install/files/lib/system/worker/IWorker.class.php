<?php
namespace wcf\system\worker;

/**
 * Every worker has to implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
interface IWorker {
	/**
	 * Creates a new worker object with additional parameters
	 * 
	 * @param	array		$parameters
	 */
	public function __construct(array $parameters);
	
	/**
	 * Sets current loop count.
	 * 
	 * @param	integer		$loopCount
	 */
	public function setLoopCount($loopCount);
	
	/**
	 * Gets current process, integer between 0 and 100. If the progress
	 * hits 100 the worker will terminate.
	 * 
	 * @return	integer
	 */
	public function getProgress();
	
	/**
	 * Executes worker action.
	 */
	public function execute();
	
	/**
	 * Returns parameters previously given within __construct().
	 * 
	 * @return	array
	 */
	public function getParameters();
	
	/**
	 * Validates parameters.
	 */
	public function validate();
	
	/**
	 * Returns URL for redirect after worker finished.
	 * 
	 * @return	string
	 */
	public function getProceedURL();
	
	/**
	 * Executes actions after worker has been executed.
	 */
	public function finalize();
}

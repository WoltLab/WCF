<?php
namespace wcf\system\worker;

/**
 * Abstract implementation of a worker.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
abstract class AbstractWorker implements IWorker {
	/**
	 * count of total actions (limited by $limit per loop)
	 * @var	integer
	 */
	protected $count = null;
	
	/**
	 * limit of actions per loop
	 * @var	integer
	 */
	protected $limit = 0;
	
	/**
	 * current loop count
	 * @var	integer
	 */
	protected $loopCount = 0;
	
	/**
	 * list of additional parameters
	 * @var	array
	 */
	protected $parameters = array();
	
	/**
	 * @see	\wcf\system\worker\IWorker::__construct()
	 */
	public function __construct(array $parameters) {
		$this->parameters = $parameters;
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getLoopCount()
	 */
	public function setLoopCount($loopCount) {
		$this->loopCount = $loopCount;
	}
	
	/**
	 * Counts objects applicable for worker action.
	 */
	abstract protected function countObjects();
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProgress()
	 */
	public function getProgress() {
		$this->countObjects();
		
		if (!$this->count) {
			return 100;
		}
		
		$progress = (($this->limit * ($this->loopCount + 1)) / $this->count) * 100;
		if ($progress > 100) $progress = 100;
		return floor($progress);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getParameters()
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::finalize()
	 */
	public function finalize() {
		// does nothing
	}
}

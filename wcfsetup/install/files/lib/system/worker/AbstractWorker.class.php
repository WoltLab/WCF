<?php
namespace wcf\system\worker;

/**
 * Basic implementation for workers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category 	Community Framework
 */
abstract class AbstractWorker implements IWorker {
	/**
	 * count of total actions (limited by $limit per loop)
	 * @var	integer
	 */
	protected $count = 0;
	
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
	 * @see	wcf\system\worker\IWorker::__construct()
	 */
	public function __construct(array $parameters) {
		$this->parameters = $parameters;
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::getLoopCount()
	 */
	public function setLoopCount($loopCount) {
		$this->loopCount = $loopCount;
	}
	
	/**
	 * Counts objects applicable for worker action.
	 */
	abstract protected function countObjects();
	
	/**
	 * @see	wcf\system\worker\IWorker::getProgress()
	 */
	public function getProgress() {
		$this->countObjects();
		
		if (!$this->count) {
			return 100;
		}
		
		return ceil(($this->loopCount / ceil($this->count / $this->limit)) * 100);
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::getParameters()
	 */
	public function getParameters() {
		return $this->parameters;
	}
}

<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Provides functions to execute cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class CronjobScheduler extends SingletonFactory {
	/**
	 * cached times of the next and after next cronjob execution
	 * @var	array<integer>
	 */
	protected $cache = array();
	
	/**
	 * list of editors for outstanding cronjobs
	 * @var	array<\wcf\data\cronjob\CronjobEditor>
	 */
	protected $cronjobEditors = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->loadCache();
	}
	
	/**
	 * Executes outstanding cronjobs.
	 */
	public function executeCronjobs() {
		// break if there are no outstanding cronjobs
		if ($this->cache['nextExec'] > TIME_NOW && $this->cache['afterNextExec'] > TIME_NOW) {
			return;
		}
		
		// get outstanding cronjobs
		$this->loadCronjobs();
		
		// clear cache
		self::clearCache();
		
		foreach ($this->cronjobEditors as $cronjobEditor) {
			// mark cronjob as being executed
			$cronjobEditor->update(array(
				'state' => Cronjob::EXECUTING
			));
			
			// create log entry
			$log = CronjobLogEditor::create(array(
				'cronjobID' => $cronjobEditor->cronjobID,
				'execTime' => TIME_NOW
			));
			$logEditor = new CronjobLogEditor($log);
			
			try {
				$this->executeCronjob($cronjobEditor, $logEditor);
			}
			catch (SystemException $e) {
				$this->logResult($logEditor, $e);
			}
			
			// get time of next execution
			$nextExec = $cronjobEditor->getNextExec();
			$afterNextExec = $cronjobEditor->getNextExec(($nextExec + 120));
			
			// mark cronjob as done
			$cronjobEditor->update(array(
				'lastExec' => TIME_NOW,
				'afterNextExec' => $afterNextExec,
				'failCount' => 0,
				'nextExec' => $nextExec,
				'state' => Cronjob::READY
			));
		}
	}
	
	/**
	 * Returns the next execution time.
	 * 
	 * @return	integer
	 */
	public function getNextExec() {
		return $this->cache['nextExec'];
	}
	
	/**
	 * Loads outstanding cronjobs.
	 */
	protected function loadCronjobs() {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_cronjob cronjob
			WHERE	(cronjob.nextExec <= ? OR cronjob.afterNextExec <= ?)
				AND cronjob.isDisabled = ?
				AND cronjob.failCount < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			TIME_NOW,
			TIME_NOW,
			0,
			Cronjob::MAX_FAIL_COUNT
		));
		while ($row = $statement->fetchArray()) {
			$cronjob = new Cronjob(null, $row);
			$cronjobEditor = new CronjobEditor($cronjob);
			$executeCronjob = true;
			
			$data = array(
				'state' => Cronjob::PENDING
			);
			
			// reset cronjob if it got stuck before and afterNextExec is in the past
			if ($cronjobEditor->afterNextExec <= TIME_NOW) {
				if ($cronjobEditor->state == Cronjob::EXECUTING) {
					$failCount = $cronjobEditor->failCount + 1;
					$data['failCount'] = $failCount;
					
					// disable cronjob
					if ($failCount == Cronjob::MAX_FAIL_COUNT) {
						$data['isDisabled'] = 1;
						$data['state'] = 0;
						$executeCronjob = false;
					}
				}
			}
			// ignore cronjobs which seem to be running
			else if ($cronjobEditor->nextExec <= TIME_NOW && $cronjobEditor->state != Cronjob::READY) {
				$executeCronjob = false;
			}
			
			// mark cronjob as pending, preventing parallel execution
			$cronjobEditor->update($data);
			
			if ($executeCronjob) {
				$this->cronjobEditors[] = $cronjobEditor;
			}
		}
	}
	
	/**
	 * Executes a cronjob.
	 * 
	 * @param	\wcf\data\cronjob\CronjobEditor		$cronjobEditor
	 * @param	\wcf\data\cronjob\log\CronjobLogEditor	$logEditor
	 */
	protected function executeCronjob(CronjobEditor $cronjobEditor, CronjobLogEditor $logEditor) {
		$className = $cronjobEditor->className;
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// verify class signature
		if (!(ClassUtil::isInstanceOf($className, 'wcf\system\cronjob\ICronjob'))) {
			throw new SystemException("'".$className."' does not implement 'wcf\system\cronjob\ICronjob'");
		}
		
		// execute cronjob
		$cronjob = new $className();
		$cronjob->execute($cronjobEditor->getDecoratedObject());
		
		$this->logResult($logEditor);
	}
	
	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	\wcf\data\cronjob\CronjobEditor		$logEditor
	 * @param	\wcf\system\exception\SystemException	$exception
	 */
	protected function logResult(CronjobLogEditor $logEditor, SystemException $exception = null) {
		if ($exception !== null) {
			$errString = implode("\n", array(
				$exception->getMessage(),
				$exception->getCode(),
				$exception->getFile(),
				$exception->getLine(),
				$exception->getTraceAsString()
			));
			
			$logEditor->update(array(
				'success' => 0,
				'error' => $errString
			));
		}
		else {
			$logEditor->update(array(
				'success' => 1
			));
		}
	}
	
	/**
	 * Loads the cached data for cronjob execution.
	 */
	protected function loadCache() {
		$this->cache = CronjobCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Clears the cronjob data cache.
	 */
	public static function clearCache() {
		CronjobCacheBuilder::getInstance()->reset();
	}
}

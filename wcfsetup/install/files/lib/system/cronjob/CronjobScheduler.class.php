<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides functions to execute cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class CronjobScheduler extends SingletonFactory {
	/**
	 * cached times of the next and after next cronjob execution
	 * @var	integer[]
	 */
	protected $cache = [];
	
	/**
	 * list of editors for outstanding cronjobs
	 * @var	CronjobEditor[]
	 */
	protected $cronjobEditors = [];
	
	/**
	 * @inheritDoc
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
			$cronjobEditor->update([
				'state' => Cronjob::EXECUTING
			]);
			
			// create log entry
			$log = CronjobLogEditor::create([
				'cronjobID' => $cronjobEditor->cronjobID,
				'execTime' => TIME_NOW
			]);
			$logEditor = new CronjobLogEditor($log);
			
			// check if all required options are set for cronjob to be executed
			// note: a general log is created to avoid confusion why a cronjob
			// apperently is not executed while that is indeed the correct internal
			// behavior
			if ($cronjobEditor->validateOptions()) {
				try {
					$this->executeCronjob($cronjobEditor, $logEditor);
				}
				catch (SystemException $e) {
					$this->logResult($logEditor, $e);
				}
			}
			else {
				$this->logResult($logEditor);
			}
			
			// get time of next execution
			$nextExec = $cronjobEditor->getNextExec();
			$afterNextExec = $cronjobEditor->getNextExec(($nextExec + 120));
			
			// mark cronjob as done
			$cronjobEditor->update([
				'lastExec' => TIME_NOW,
				'afterNextExec' => $afterNextExec,
				'failCount' => 0,
				'nextExec' => $nextExec,
				'state' => Cronjob::READY
			]);
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
		$statement->execute([
			TIME_NOW,
			TIME_NOW,
			0,
			Cronjob::MAX_FAIL_COUNT
		]);
		while ($row = $statement->fetchArray()) {
			$cronjob = new Cronjob(null, $row);
			$cronjobEditor = new CronjobEditor($cronjob);
			$executeCronjob = true;
			
			$data = [
				'state' => Cronjob::PENDING
			];
			
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
	 * @param	CronjobEditor		$cronjobEditor
	 * @param	CronjobLogEditor	$logEditor
	 * @throws	SystemException
	 */
	protected function executeCronjob(CronjobEditor $cronjobEditor, CronjobLogEditor $logEditor) {
		$className = $cronjobEditor->className;
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// verify class signature
		if (!(is_subclass_of($className, ICronjob::class))) {
			throw new ImplementationException($className, ICronjob::class);
		}
		
		// execute cronjob
		$cronjob = new $className();
		$cronjob->execute($cronjobEditor->getDecoratedObject());
		
		$this->logResult($logEditor);
	}
	
	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	CronjobLogEditor	$logEditor
	 * @param	SystemException		$exception
	 */
	protected function logResult(CronjobLogEditor $logEditor, SystemException $exception = null) {
		if ($exception !== null) {
			$errString = implode("\n", [
				$exception->getMessage(),
				$exception->getCode(),
				$exception->getFile(),
				$exception->getLine(),
				$exception->getTraceAsString()
			]);
			
			$logEditor->update([
				'success' => 0,
				'error' => $errString
			]);
		}
		else {
			$logEditor->update([
				'success' => 1
			]);
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

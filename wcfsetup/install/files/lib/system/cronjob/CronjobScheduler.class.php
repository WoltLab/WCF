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
use function wcf\functions\exception\logThrowable;

/**
 * Provides functions to execute cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
		
		$this->resetFailedCronjobs();
		
		// get outstanding cronjobs
		$cronjobEditors = $this->loadCronjobs();
		
		// clear cache
		self::clearCache();
		
		foreach ($cronjobEditors as $cronjobEditor) {
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
			// apparently is not executed while that is indeed the correct internal
			// behavior
			if ($cronjobEditor->validateOptions()) {
				try {
					$this->executeCronjob($cronjobEditor, $logEditor);
				}
				catch (\Throwable $e) {
					$this->logResult($logEditor, $e);
				}
				catch (\Exception $e) {
					$this->logResult($logEditor, $e);
				}
			}
			else {
				$this->logResult($logEditor);
			}
			
			// mark cronjob as done
			$cronjobEditor->update([
				'failCount' => 0,
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
	 * Resets any cronjobs that have previously failed to execute. Cronjobs that have failed too often will
	 * be disabled automatically.
	 */
	protected function resetFailedCronjobs() {
		WCF::getDB()->beginTransaction();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$committed = false;
		try {
			$sql = "SELECT		*
				FROM		wcf" . WCF_N . "_cronjob
				WHERE		state <> ?
					AND	isDisabled = ?
					AND	afterNextExec <= ?
				FOR UPDATE";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				Cronjob::READY,
				0,
				TIME_NOW
			]);
			/** @var Cronjob $cronjob */
			while ($cronjob = $statement->fetchObject(Cronjob::class)) {
				// In any case: Reset the state to READY.
				$data['state'] = Cronjob::READY;
				
				switch ($cronjob->state) {
					/** @noinspection PhpMissingBreakStatementInspection */
					case Cronjob::EXECUTING:
						// The cronjob spent two periods in the EXECUTING state.
						// We must assume it crashed.
						$data['failCount'] = $cronjob->failCount + 1;
						
						// The cronjob exceeded the maximum fail count.
						// Thus it must be disabled.
						if ($data['failCount'] > Cronjob::MAX_FAIL_COUNT) {
							$data['isDisabled'] = 1;
							$data['failCount'] = 0;
						}
						// fall through
					case Cronjob::PENDING:
						// The cronjob spent two periods in the PENDING state.
						// We must assume a previous cronjob in the same request hosed
						// the whole process (e.g. by exceeding the memory limit).
						// This is not the fault of this cronjob, thus the fail counter
						// is not being increased.
						
						$log = CronjobLogEditor::create([
							'cronjobID' => $cronjob->cronjobID,
							'execTime' => TIME_NOW
						]);
						$logEditor = new CronjobLogEditor($log);
						$this->logResult($logEditor, new \Exception("Cronjob stuck in state '".$cronjob->state."' for two periods (nextExec '".$cronjob->nextExec."', afterNextExec '".$cronjob->afterNextExec."', now '".TIME_NOW."')."));
						break;
					default:
						throw new \LogicException('Unreachable');
				}
				
				// Schedule the cronjob for execution at the next regular execution date. The previous
				// implementation was executing the cronjob immediately, which may be undesirable if
				// the cronjob is expected to be executed in a specific time window only. 
				$data['nextExec'] = $cronjob->getNextExec(TIME_NOW);
				$data['afterNextExec'] = $cronjob->getNextExec($data['nextExec'] + 120);
				
				(new CronjobEditor($cronjob))->update($data);
			}
			
			WCF::getDB()->commitTransaction();
			$committed = true;
		}
		finally {
			if (!$committed) {
				WCF::getDB()->rollBackTransaction();
			}
		}
	}
	
	/**
	 * Loads outstanding cronjobs.
	 */
	protected function loadCronjobs() {
		WCF::getDB()->beginTransaction();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$committed = false;
		try {
			$sql = "SELECT	        *
				FROM	        wcf" . WCF_N . "_cronjob
				WHERE	        isDisabled = ?
						AND state = ?
						AND nextExec <= ?
				FOR UPDATE";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				0,
				Cronjob::READY,
				TIME_NOW
			]);
			
			$cronjobEditors = [];
			/** @var Cronjob $cronjob */
			while ($cronjob = $statement->fetchObject(Cronjob::class)) {
				$cronjobEditor = new CronjobEditor($cronjob);
				
				// Mark the cronjob as pending to prevent concurrent requests from executing it.
				$data = ['state' => Cronjob::PENDING];
				
				// Update next execution time.
				// This needs to be done before executing the job to allow for a proper detection of
				// stuck jobs. If the timestamps are updated after executing then concurrent requests
				// might believe that a cronjob is stuck, despite the cronjob just having started just
				// a few milliseconds before.
				$data['nextExec'] = $cronjob->getNextExec();
				$data['afterNextExec'] = $cronjob->getNextExec($data['nextExec'] + 120);
				$data['lastExec'] = TIME_NOW;
				
				$cronjobEditor->update($data);
				
				$cronjobEditors[] = $cronjobEditor;
			}
			WCF::getDB()->commitTransaction();
			$committed = true;
			
			return $cronjobEditors;
		}
		finally {
			if (!$committed) {
				WCF::getDB()->rollBackTransaction();
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
		if (!is_subclass_of($className, ICronjob::class)) {
			throw new ImplementationException($className, ICronjob::class);
		}
		
		// execute cronjob
		/** @var ICronjob $cronjob */
		$cronjob = new $className();
		$cronjob->execute($cronjobEditor->getDecoratedObject());
		
		$this->logResult($logEditor);
	}
	
	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	CronjobLogEditor	$logEditor
	 * @param	\Throwable		$exception
	 */
	protected function logResult(CronjobLogEditor $logEditor, $exception = null) {
		if ($exception !== null) {
			logThrowable($exception);
			
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

<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Provides functions to execute cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CronjobScheduler extends SingletonFactory {
	/**
	 * cached times of the next and after next cronjob execution
	 * @var	array<integer>
	 */
	protected $cache = array();
	
	/**
	 * list of editors for outstanding cronjobs
	 * @var	array<wcf\data\cronjob\CronjobEditor>
	 */
	protected $cronjobEditors = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
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
			$afterNextExec = $cronjobEditor->getNextExec($nextExec);
			
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
	 * Loads outstanding cronjobs.
	 */
	protected function loadCronjobs() {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("cronjob.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$conditions->add("(cronjob.nextExec <= ? OR cronjob.afterNextExec <= ?)", array(TIME_NOW, TIME_NOW));
		$conditions->add("cronjob.active = ?", array(1));
		$conditions->add("cronjob.failCount < ?", array(3));
		$conditions->add("cronjob.state = ?", array(Cronjob::READY));
		
		$sql = "SELECT		cronjob.*
			FROM		wcf".WCF_N."_cronjob cronjob
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$cronjob = new Cronjob(null, $row);
			$cronjobEditor = new CronjobEditor($cronjob);
			$executeCronjob = true;
			
			$data = array(
				'state' => Cronjob::PENDING
			);
			
			// reset cronjob if it got stuck before and afterNextExec is in the past
			if ($cronjobEditor->afterNextExec <= TIME_NOW && $cronjobEditor->state == Cronjob::EXECUTING) {
				$failCount = $cronjobEditor->failCount + 1;
				$data['failCount'] = $failCount;
				
				// disable cronjob
				if ($failCount == 3) {
					$data['active'] = 0;
					$executeCronjob = false;
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
	 * @param	wcf\data\cronjob\CronjobEditor		$cronjobEditor
	 * @param	wcf\data\cronjob\log\CronjobLogEditor	$logEditor
	 */
	protected function executeCronjob(CronjobEditor $cronjobEditor, CronjobLogEditor $logEditor) {
		$className = $cronjobEditor->className;
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// verify class signature
		if (!(ClassUtil::isInstanceOf($className, 'wcf\system\cronjob\ICronjob'))) {
			throw new SystemException("class '".$className."' does not implement the interface 'wcf\system\cronjob\ICronjob'");
		}
		
		// execute cronjob
		$cronjob = new $className();
		$cronjob->execute($cronjobEditor->getDecoratedObject());
		
		$this->logResult($logEditor);
	}
	
	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	wcf\data\cronjob\CronjobEditor		$logEditor
	 * @param	wcf\system\exception\SystemException	$exception
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
		$cacheName = 'cronjobs-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\CronjobCacheBuilder'
		);
		$this->cache = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Clears the cronjob data cache.
	 */
	public static function clearCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.cronjobs-'.PACKAGE_ID.'.php');
	}
}

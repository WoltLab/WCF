<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\cronjob\Cronjob AS CronjobObj;
use wcf\data\cronjob\CronjobEditor;
use wcf\system\cache\CacheHandler;
use wcf\system\database\condition\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageDependencyHandler;
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
abstract class CronjobScheduler {
	/**
	 * list of outstanding cronjobs
	 * 
	 * @var	array<CronjobEditor>
	 */
	protected static $cronjobs = array();
	
	/**
	 * Executes outstanding cronjobs.
	 */
	public static function execute() {
		$cache = self::getCache();
		
		// break if there are no outstanding cronjobs
		if ($cache['nextExec'] > TIME_NOW && $cache['afterNextExec'] > TIME_NOW) return;
		
		// get outstanding cronjobs
		self::loadCronjobs();
		
		// clear cache
		self::clearCache();
		
		foreach (self::$cronjobs as $cronjob) {
			// mark cronjob as being executed
			$cronjobEditor->update(array(
				'state' => CronjobObj::EXECUTING
			));
			
			// create log entry
			$log = CronjobLogEditor::create(array(
				'cronjobID' => $cronjobEditor->cronjobID,
				'execTime' => TIME_NOW
			));
			$logEditor = new CronjobLogEditor($log);
			
			try {
				self::executeCronjob($cronjob, $logEditor);
			}
			catch (SystemException $e) {
				self::logResult($logEditor, $e);
			}
			
			// get time of next execution
			$nextExec = $cronjobEditor->getNextExec();
			$afterNextExec = $cronjobEditor->getNextExec($nextExec);
			
			// mark cronjob as done
			$cronjobEditor->update(array(
				'afterNextExec' => $afterNextExec,
				'failCount' => 0,
				'nextExec' => $nextExec,
				'state' => CronjobObj::READY
			));
		}
	}
	
	/**
	 * Loads and executes outstanding cronjobs.
	 */
	protected static function loadCronjobs() {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("cronjob.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$conditions->add("(cronjob.nextExec <= ? OR cronjob.afterNextExec <= ?)", array(TIME_NOW, TIME_NOW));
		$conditions->add("cronjob.active = ?", array(1));
		$conditions->add("cronjob.failCount < ?", array(3));
		$conditions->add("cronjob.state = ?", array(CronjobObj::READY));
		
		$sql = "SELECT		cronjob.*, package.packageDir
			FROM		wcf".WCF_N."_cronjob cronjob
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = cronjob.packageID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$cronjob = new CronjobObj(null, $row);
			$cronjobEditor = new CronjobEditor($cronjob);
			$executeCronjob = true;
			
			$data = array(
				'state' => CronjobObj::PENDING
			);
			
			// reset cronjob if it got stuck before and afterNextExec is in the past
			if ($cronjobEditor->afterNextExec <= TIME_NOW && $cronjobEditor->state == CronjobObj::EXECUTING) {
				$failCount = $cronjobEditor->failCount + 1;
				$data['failCount'] = $failCount;
				
				// disable cronjob
				if ($failCount == 3) {
					$data['active'] = 0;
					$executeCronjob = false;
				}
			}
			// ignore cronjobs which seem to be running
			else if ($cronjobEditor->nextExec <= TIME_NOW && $cronjobEditor->state != CronjobObj::READY) {
				$executeCronjob = false;
			}
			
			// mark cronjob as pending, preventing parallel execution
			$cronjobEditor->update($data);
			
			if ($executeCronjob) {
				self::$cronjobs[] = $cronjobEditor;
			}
		}
	}
	
	/**
	 * Executes a cronjob.
	 * 
	 * @param	CronjobEditor		$cronjobEditor
	 * @param	CronjobLogEditor	$logEditor
	 */
	protected static function executeCronjob(CronjobEditor $cronjobEditor, CronjobLogEditor $logEditor) {
		$className = $cronjobEditor->className;
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'", 11001);
		}
		
		// verify class signature
		if (!(ClassUtil::isInstanceOf($className, 'wcf\system\cronjob\Cronjob'))) {
			throw new SystemException("class '".$className."' does not implement the interface 'Cronjob'", 11010);
		}
		
		// execute cronjob
		$cronjob = new $className();
		$cronjob->execute();
		
		self::logResult($logEditor);
	}
	
	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	CronjobLogEditor	$log
	 * @param	SystemException		$e
	 */
	protected static function logResult(CronjobLogEditor $log, SystemException $e = null) {
		if ($exception !== null) {
			$errString = implode("\n", array(
				$e->getMessage(),
				$e->getCode(),
				$e->getFile(),
				$e->getLine(),
				$e->getTraceAsString()
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
	 * Returns cached cronjob data.
	 * 
	 * @return	array
	 */
	protected static function getCache() {
		$cacheName = 'cronjobs-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', 'wcf\system\cache\CacheBuilderCronjob');
		
		return CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * Clears cronjob cache.
	 */
	public static function clearCache() {
		// clear cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.cronjobs-'.PACKAGE_ID.'php');
	}
}
?>

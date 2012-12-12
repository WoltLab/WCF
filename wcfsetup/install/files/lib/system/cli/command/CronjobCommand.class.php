<?php
namespace wcf\system\cli\command;
use wcf\data\cronjob\CronjobAction;
use wcf\data\cronjob\CronjobList;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\exception\SystemException;
use wcf\system\CLIWCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;
use phpline\internal\Log;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;


/**
 * Executes cronjobs.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CronjobCommand implements ICommand {
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute(array $parameters) {
		$argv = new ArgvParser(array(
			'f|force' => 'Force the execution of the cronjob(s)',
			'a|all' => 'Execute all cronjobs that are over time',
			'l|list' => 'Lists available cronjobs'
		));
		$argv->setArguments($parameters);
		$argv->parse();
		
		if ($argv->list) {
			CLIWCF::getReader()->println(CLIWCF::generateTable($this->generateList()));
			return;
		}
		
		// TODO: Executing is currently very much broken. Refer to issue WoltLab/WCF#910
		$cronjobIDs = $argv->getRemainingArgs();
		if (empty($cronjobIDs) && !$argv->all) {
			throw new ArgvException('Neither cronjobIDs nor --all given. Aborting', $argv->getUsageMessage());
		}
		
		$cronjobList = new CronjobList();
		if (!$argv->all) {
			$cronjobList->getConditionBuilder()->add('cronjobID IN(?)', array($cronjobIDs));
		}
		$cronjobList->readObjects();
		if (!$argv->all) {
			foreach ($cronjobIDs as $cronjobID) {
				try {
					$cronjobList->seekTo($cronjobID);
				}
				catch (SystemException $e) {
					Log::warn('Cannot find cronjob #', $cronjobID);
				}
			}
		}
		
		$cronjobs = array();
		foreach ($cronjobList as $cronjob) {
			if ($argv->force) {
				$cronjobs[] = $cronjob;
				
				if (VERBOSITY >= 0) {
					Log::info("Executing cronjob #", $cronjob->cronjobID, ': ', StringUtil::truncate(CLIWCF::getLanguage()->get($cronjob->description), 40));
				}
			}
			else if ($cronjob->nextExec < time()) { // use time() instead of TIME_NOW, as the latter may be outdated
				$cronjobs[] = $cronjob;
				if (VERBOSITY >= 0) {
					Log::info("Executing cronjob #", $cronjob->cronjobID, ': ', StringUtil::truncate(CLIWCF::getLanguage()->get($cronjob->description), 40));
				}
			}
			else {
				if (VERBOSITY >= 0) {
					Log::info("Skipping cronjob #", $cronjob->cronjobID, ': ', StringUtil::truncate(CLIWCF::getLanguage()->get($cronjob->description), 40));
				}
			}
		}
		
		$action = new CronjobAction($cronjobs, 'execute');
		$action->executeAction();
		$action = new CronjobAction($cronjobs, 'update', array(
			'data' => array(
				'lastExec' => time()
			)
		));
		$action->executeAction();
	}
	
	/**
	 * Returns an array with a list of cronjobs.
	 *
	 * @return array
	 */
	public function generateList() {
		$cronjobList = new CronjobList();
		$cronjobList->readObjects();
		
		$table = array(array(
			CLIWCF::getLanguage()->get('wcf.global.objectID'),
			CLIWCF::getLanguage()->get('wcf.acp.cronjob.description'),
			CLIWCF::getLanguage()->get('wcf.acp.cronjob.nextExec')
		));
		
		foreach ($cronjobList as $cronjob) {
			$dateTimeObject = DateUtil::getDateTimeByTimestamp($cronjob->nextExec);
			$date = DateUtil::format($dateTimeObject, DateUtil::DATE_FORMAT);
			$time = DateUtil::format($dateTimeObject, DateUtil::TIME_FORMAT);
			$dateTime = str_replace('%time%', $time, str_replace('%date%', $date, CLIWCF::getLanguage()->get('wcf.date.dateTimeFormat')));
			
			$table[] = array(
				$cronjob->cronjobID,
				CLIWCF::getLanguage()->get($cronjob->description),
				$dateTime
			);
		}
		
		return $table;
	}
	
	/**
	 * @see \wcf\system\cli\command\ICommand::canAccess()
	 */
	public function canAccess() {
		// TODO: Check access
		return true;
	}
}
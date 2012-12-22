<?php
namespace wcf\system\cli\command;
use wcf\system\CLIWCF;
use wcf\system\Regex;
use wcf\util\ClassUtil;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;
use phpline\internal\Log;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console as ConsoleProgressBar;


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
class WorkerCommand implements ICommand {
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute(array $parameters) {
		$argv = new ArgvParser(array(
			'l|list' => 'Lists available workers',
			'setParameter=s' => 'Sets a parameter given to the worker'
		));
		$argv->setOptions(array(
			ArgvParser::CONFIG_FREEFORM_FLAGS => true,
			ArgvParser::CONFIG_PARSEALL => false,
			ArgvParser::CONFIG_CUMULATIVE_PARAMETERS => true
		));
		$argv->setArguments($parameters);
		$argv->parse();
		
		if ($argv->list) {
			CLIWCF::getReader()->println(CLIWCF::generateTable($this->generateList()));
			return;
		}
		
		$args = $argv->getRemainingArgs();
		// validate parameters
		if (count($args) != 1) {
			throw new ArgvException('', str_replace($_SERVER['argv'][0].' [ options ]', $_SERVER['argv'][0].' [ options ] <worker>', $argv->getUsageMessage()));
		}
		
		$class = $args[0];
		
		// assume wcf\system\worker when no FQN ist given
		if (strpos($class, '\\') === false) {
			$class = 'wcf\system\worker\\'.$class;
		}
		
		$invalid = false;
		if (!class_exists($class)) {
			$invalid = true;
		}
		else {
			$reflection = new \ReflectionClass($class);
			if (!$reflection->isInstantiable()) {
				$invalid = true;
			}
			else if (!ClassUtil::isInstanceOf($class, 'wcf\system\worker\IWorker')) {
				$invalid = true;
			}
		}
		if ($invalid) {
			throw new ArgvException("Invalid worker '".$class."' given", $argv->getUsageMessage());
		}
		
		// parse parameters
		$options = $argv->getOptions();
		$parameters = array();
		foreach ($options as $option) {
			$value = $argv->getOption($option);
			if ($option === 'setParameter') {
				if (!is_array($value)) {
					$value = array($value);
				}
				
				foreach ($value as $parameter) {
					list($parameterKey, $parameterValue) = explode('=', $parameter);
					$parameters[$parameterKey] = $parameterValue;
				}
			}
			else {
				$parameters[$option] = $value;
			}
		}
		
		$worker = new $class($parameters);
		$worker->validate();
		$progress = $worker->getProgress();
		
		// initialize progressbar
		$progressbar = new ProgressBar(new ConsoleProgressBar(array(
			'width' => CLIWCF::getTerminal()->getWidth()
		)));
		for ($i = 0; $progress < 100; $i++) {
			$worker->setLoopCount($i);
			$worker->validate();
			
			// update progress
			$progress = $worker->getProgress();
			$progressbar->update($progress);
			
			// execute worker
			$worker->execute();
		}
		$progressbar->update($progress);
	}
	
	/**
	 * Returns an array with a list of workers.
	 * 
	 * @return array
	 */
	public function generateList() {
		$directory = DirectoryUtil::getInstance(WCF_DIR.'lib/system/worker/');
		$workerList = $directory->getFiles(SORT_ASC, new Regex('Worker\.class\.php$'));
			
		$table = array(
			array(
				'Class',
				'Description'
			)
		);
		foreach ($workerList as $worker) {
			$class = 'wcf\system\worker\\'.basename($worker, '.class.php');
			if (!class_exists($class) && !interface_exists($class)) {
				Log::info('Invalid worker file: ', $worker);
				continue;
			}
			$reflection = new \ReflectionClass($class);
			if (!$reflection->isInstantiable()) continue;
		
			if (!ClassUtil::isInstanceOf($class, 'wcf\system\worker\IWorker')) {
				Log::info('Invalid worker file: ', $worker);
				continue;
			}
		
			$docComment = explode("\n", StringUtil::unifyNewlines($reflection->getDocComment()));
			foreach ($docComment as $commentLine) {
				if (Regex::compile('[a-z]', Regex::CASE_INSENSITIVE)->match($commentLine)) {
					$comment = Regex::compile('^[^a-z]+', Regex::CASE_INSENSITIVE)->replace($commentLine, '');
					break;
				}
			}
		
			$table[] = array(
				basename($worker, '.class.php'),
				$comment
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

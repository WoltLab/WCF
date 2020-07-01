<?php
namespace wcf\system\cli\command;
use phpline\internal\Log;
use wcf\system\worker\IWorker;
use wcf\system\CLIWCF;
use wcf\system\Regex;
use wcf\util\CLIUtil;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\ProgressBar\Adapter\Console as ConsoleProgressBar;
use Zend\ProgressBar\ProgressBar;

/**
 * Executes cronjobs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
class WorkerCLICommand implements IArgumentedCLICommand {
	/**
	 * arguments parser
	 * @var	\Zend\Console\Getopt
	 */
	protected $argv = null;
	
	/**
	 * Initializes the argument parser.
	 */
	public function __construct() {
		$this->argv = new ArgvParser([
			'l|list' => CLIWCF::getLanguage()->get('wcf.cli.worker.list'),
			'setParameter=s' => CLIWCF::getLanguage()->get('wcf.cli.worker.setParameter')
		]);
		$this->argv->setOptions([
			ArgvParser::CONFIG_FREEFORM_FLAGS => true,
			ArgvParser::CONFIG_PARSEALL => false,
			ArgvParser::CONFIG_CUMULATIVE_PARAMETERS => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $parameters) {
		$this->argv->setArguments($parameters);
		$this->argv->parse();
		
		if ($this->argv->list) {
			CLIWCF::getReader()->println(CLIUtil::generateTable($this->generateList()));
			return;
		}
		
		$args = $this->argv->getRemainingArgs();
		// validate parameters
		if (count($args) != 1) {
			throw new ArgvException('', $this->getUsage());
		}
		
		$class = $args[0];
		
		// assume wcf\system\worker when no FQN is given
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
			else if (!is_subclass_of($class, IWorker::class)) {
				$invalid = true;
			}
		}
		if ($invalid) {
			throw new ArgvException("Invalid worker '".$class."' given", $this->getUsage());
		}
		
		// parse parameters
		$options = $this->argv->getOptions();
		$parameters = [];
		foreach ($options as $option) {
			$value = $this->argv->getOption($option);
			if ($option === 'setParameter') {
				if (!is_array($value)) {
					$value = [$value];
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
		
		/** @var IWorker $worker */
		$worker = new $class($parameters);
		$worker->validate();
		$worker->getProgress(); // make sure objects are counted
		
		// initialize progressbar
		$progressbar = new ProgressBar(new ConsoleProgressBar([
			'width' => CLIWCF::getTerminal()->getWidth()
		]));
		$progress = 0;
		$output = null;
		if (!empty($_ENV['WORKER_STATUS_FD'])) {
			$output = new File("php://fd/".$_ENV['WORKER_STATUS_FD'], "w");
		}
		for ($i = 0; $progress < 100; $i++) {
			$worker->setLoopCount($i);
			$worker->validate();
			
			// execute worker
			$worker->execute();
			$worker->finalize();
			
			// update progress
			$progress = $worker->getProgress();
			$progressbar->update($progress);
			if ($output) {
				$output->write("$i,$progress\n");
			}
		}
		if ($output) {
			$output->write("done,$progress");
			$output->close();
		}
		$progressbar->update($progress);
		
		$progressbar->finish();
	}
	
	/**
	 * Returns an array with the list of all available workers.
	 * 
	 * @return	array
	 */
	public function generateList() {
		$directory = DirectoryUtil::getInstance(WCF_DIR.'lib/system/worker/');
		$workerList = $directory->getFiles(SORT_ASC, new Regex('Worker\.class\.php$'));
		
		$table = [
			[
				'Class',
				'Description'
			]
		];
		foreach ($workerList as $worker) {
			$class = 'wcf\system\worker\\'.basename($worker, '.class.php');
			if (!class_exists($class) && !interface_exists($class)) {
				Log::info('Invalid worker file: ', $worker);
				continue;
			}
			$reflection = new \ReflectionClass($class);
			if (!$reflection->isInstantiable()) continue;
			
			if (!is_subclass_of($class, IWorker::class)) {
				Log::info('Invalid worker file: ', $worker);
				continue;
			}
			
			$docComment = explode("\n", StringUtil::unifyNewlines($reflection->getDocComment()));
			$comment = '';
			foreach ($docComment as $commentLine) {
				if (Regex::compile('[a-z]', Regex::CASE_INSENSITIVE)->match($commentLine)) {
					$comment = Regex::compile('^[^a-z]+', Regex::CASE_INSENSITIVE)->replace($commentLine, '');
					break;
				}
			}
			
			$table[] = [
				basename($worker, '.class.php'),
				$comment
			];
		}
		
		return $table;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsage() {
		return str_replace($_SERVER['argv'][0].' [ options ]', 'worker [ options ] <worker>', $this->argv->getUsageMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return true;
	}
}

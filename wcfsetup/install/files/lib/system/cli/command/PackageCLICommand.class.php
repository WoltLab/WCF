<?php
namespace wcf\system\cli\command;
use phpline\internal\Log;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\CLIWCF;
use wcf\util\FileUtil;
use wcf\util\JSON;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\ProgressBar\Adapter\Console as ConsoleProgressBar;
use Zend\ProgressBar\ProgressBar;

/**
 * Executes package installation.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
class PackageCLICommand implements IArgumentedCLICommand {
	/**
	 * arguments parser
	 * @var	\Zend\Console\Getopt
	 */
	protected $argv = null;
	
	/**
	 * Initializes the argument parser.
	 */
	public function __construct() {
		$this->argv = new ArgvParser([]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $parameters) {
		$this->argv->setArguments($parameters);
		$this->argv->parse();
		
		if (count($this->argv->getRemainingArgs()) !== 2) {
			throw new ArgvException('', $this->fixUsage($this->argv->getUsageMessage()));
		}
		
		list($action, $package) = $this->argv->getRemainingArgs();
		CLIWCF::getReader()->setHistoryEnabled(false);
		
		switch ($action) {
			case 'install':
				$this->install($package);
			break;
			
			case 'uninstall':
				$this->uninstall($package);
			break;
			
			default:
				throw new ArgvException('', $this->fixUsage($this->argv->getUsageMessage()));
			break;
		}
	}
	
	/**
	 * Installs the specified package.
	 * 
	 * @param	string	$file
	 */
	private function install($file) {
		// PackageStartInstallForm::validateDownloadPackage()
		if (FileUtil::isURL($file)) {
			// download package
			$archive = new PackageArchive($file, null);
			
			try {
				if (VERBOSITY >= 1) Log::info("Downloading '".$file."'");
				$file = $archive->downloadArchive();
			}
			catch (SystemException $e) {
				$this->error('notFound', ['file' => $file]);
			}
		}
		else {
			// probably local path
			if (!file_exists($file)) {
				$this->error('notFound', ['file' => $file]);
			}
			
			$archive = new PackageArchive($file, null);
		}
		
		// PackageStartInstallForm::validateArchive()
		// try to open the archive
		try {
			// TODO: Exceptions thrown within openArchive() are discarded, resulting in
			// the meaningless message 'not a valid package'
			$archive->openArchive();
		}
		catch (SystemException $e) {
			$this->error('noValidPackage');
		}
		$errors = PackageInstallationDispatcher::validatePHPRequirements($archive->getPhpRequirements());
		if (!empty($errors)) {
			// TODO: Nice output
			$this->error('phpRequirements', ['errors' => $errors]);
		}
		
		// try to find existing package
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = CLIWCF::getDB()->prepareStatement($sql);
		$statement->execute([$archive->getPackageInfo('name')]);
		$row = $statement->fetchArray();
		$package = null;
		if ($row !== false) {
			$package = new Package(null, $row);
		}
		
		// check update or install support
		if ($package !== null) {
			CLIWCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);
			
			$archive->setPackage($package);
			if (!$archive->isValidUpdate()) {
				$this->error('noValidUpdate');
			}
		}
		else {
			CLIWCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
			
			if (!$archive->isValidInstall()) {
				$this->error('noValidInstall');
			}
			else if ($archive->getPackageInfo('isApplication')) {
				// applications cannot be installed via CLI
				$this->error('cli.installIsApplication');
			}
			else if ($archive->isAlreadyInstalled()) {
				$this->error('uniqueAlreadyInstalled');
			}
			else if ($archive->getPackageInfo('isApplication') && $archive->hasUniqueAbbreviation()) {
				$this->error('noUniqueAbbrevation');
			}
		}
		
		// PackageStartInstallForm::save()
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// insert queue
		PackageInstallationQueueEditor::create([
			'processNo' => $processNo,
			'userID' => CLIWCF::getUser()->userID,
			'package' => $archive->getPackageInfo('name'),
			'packageName' => $archive->getLocalizedPackageInfo('packageName'),
			'packageID' => ($package !== null) ? $package->packageID : null,
			'archive' => $file,
			'action' => ($package !== null ? 'update' : 'install')
		]);
		
		// PackageInstallationDispatcher::openQueue()
		$parentQueueID = 0;
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", [CLIWCF::getUser()->userID]);
		$conditions->add("parentQueueID = ?", [$parentQueueID]);
		if ($processNo != 0) $conditions->add("processNo = ?", [$processNo]);
		$conditions->add("done = ?", [0]);
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_queue
			".$conditions."
			ORDER BY	queueID ASC";
		$statement = CLIWCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageInstallation = $statement->fetchArray();
		if (!isset($packageInstallation['queueID'])) {
			$this->error('internalOpenQueue');
			return;
		}
		else {
			$queueID = $packageInstallation['queueID'];
		}
		
		// PackageInstallationConfirmPage::readParameters()
		$queue = new PackageInstallationQueue($queueID);
		if (!$queue->queueID || $queue->done) {
			$this->error('internalReadParameters');
			return;
		}
		
		// PackageInstallationConfirmPage::readData()
		$missingPackages = 0;
		$packageInstallationDispatcher = new PackageInstallationDispatcher($queue);
		
		// get requirements
		$requirements = $packageInstallationDispatcher->getArchive()->getRequirements();
		$openRequirements = $packageInstallationDispatcher->getArchive()->getOpenRequirements();
		
		foreach ($requirements as &$requirement) {
			if (isset($openRequirements[$requirement['name']])) {
				$requirement['status'] = 'missing';
				$requirement['action'] = $openRequirements[$requirement['name']]['action'];
				
				if (!isset($requirement['file'])) {
					if ($requirement['action'] === 'update') {
						$requirement['status'] = 'missingVersion';
						$requirement['existingVersion'] = $openRequirements[$requirement['name']]['existingVersion'];
					}
					$missingPackages++;
				}
				else {
				$requirement['status'] = 'delivered';
					$packageArchive = new PackageArchive($packageInstallationDispatcher->getArchive()->extractTar($requirement['file']));
					$packageArchive->openArchive();
					
					// make sure that the delivered package is correct
					if ($requirement['name'] != $packageArchive->getPackageInfo('name')) {
						$requirement['status'] = 'invalidDeliveredPackage';
						$requirement['deliveredPackage'] = $packageArchive->getPackageInfo('name');
						$missingPackages++;
					}
					else if (isset($requirement['minversion'])) {
						// make sure that the delivered version is sufficient
						if (Package::compareVersion($requirement['minversion'], $packageArchive->getPackageInfo('version')) > 0) {
							$requirement['deliveredVersion'] = $packageArchive->getPackageInfo('version');
							$requirement['status'] = 'missingVersion';
							$missingPackages++;
						}
					}
				}
			}
			else {
				$requirement['status'] = 'installed';
			}
		}
		unset($requirement);
		
		// PackageInstallationConfirmPage::assignVariables/show()
		$excludingPackages = $packageInstallationDispatcher->getArchive()->getConflictedExcludingPackages();
		$excludedPackages = $packageInstallationDispatcher->getArchive()->getConflictedExcludedPackages();
		if (!($missingPackages == 0 && count($excludingPackages) == 0 && count($excludedPackages) == 0)) {
			$this->error('missingPackagesOrExclude', [
				'requirements' => $requirements,
				'excludingPackages' => $excludingPackages,
				'excludedPackages' => $excludedPackages
			]);
			return;
		}
		
		// AbstractDialogAction::readParameters()
		$step = 'prepare';
		$queueID = $queue->queueID;
		$node = '';
		
		// initialize progressbar
		$progressbar = new ProgressBar(new ConsoleProgressBar([
			'width' => CLIWCF::getTerminal()->getWidth(),
			'elements' => [
				ConsoleProgressBar::ELEMENT_PERCENT,
				ConsoleProgressBar::ELEMENT_BAR,
				ConsoleProgressBar::ELEMENT_TEXT
			],
			'textWidth' => min(floor(CLIWCF::getTerminal()->getWidth() / 2), 50)
		]));
		
		// InstallPackageAction::readParameters()
		$finished = false;
		while (!$finished) {
			$queue = new PackageInstallationQueue($queueID);
			
			if (!$queue->queueID) {
				// todo: what to output?
				echo "InstallPackageAction::readParameters()";
				return;
			}
			$installation = new PackageInstallationDispatcher($queue);
			
			$progress = 0;
			$currentAction = '';
			switch ($step) {
				case 'prepare':
					// InstallPackageAction::stepPrepare()
					// update package information
					$installation->updatePackage();
					
					// clean-up previously created nodes
					$installation->nodeBuilder->purgeNodes();
					
					// create node tree
					$installation->nodeBuilder->buildNodes();
					$node = $installation->nodeBuilder->getNextNode();
					$queueID = $installation->nodeBuilder->getQueueByNode($installation->queue->processNo, $node);
					
					$step = 'install';
					$progress = 0;
					$currentAction = $installation->nodeBuilder->getPackageNameByQueue($queueID);
				break;
				
				case 'install':
					// InstallPackageAction::stepInstall()
					$step_ = $installation->install($node);
					$queueID = $installation->nodeBuilder->getQueueByNode($installation->queue->processNo, $step_->getNode());
					
					if ($step_->hasDocument()) {
						$progress = $installation->nodeBuilder->calculateProgress($node);
						$node = $step_->getNode();
						$currentAction = $installation->nodeBuilder->getPackageNameByQueue($queueID);
					}
					else {
						if ($step_->getNode() == '') {
							// perform final actions
							$installation->completeSetup();
							// InstallPackageAction::finalize()
							CacheHandler::getInstance()->flushAll();
							// /InstallPackageAction::finalize()
							
							// show success
							$progress = 100;
							$currentAction = CLIWCF::getLanguage()->get('wcf.acp.package.installation.step.install.success');
							$finished = true;
							continue;
						}
						else {
							// continue with next node
							$progress = $installation->nodeBuilder->calculateProgress($node);
							$node = $step_->getNode();
							$currentAction = $installation->nodeBuilder->getPackageNameByQueue($queueID);
						}
					}
					break;
			}
			
			$progressbar->update($progress, $currentAction);
		}
		
		$progressbar->getAdapter()->finish();
	}
	
	/**
	 * Uninstalls the specified package.
	 * $package may either be the packageID or the package identifier.
	 * 
	 * @param	mixed	$package
	 */
	private function uninstall($package) {
		if (Package::isValidPackageName($package)) {
			$packageID = PackageCache::getInstance()->getPackageID($package);
		}
		else {
			$packageID = $package;
		}
		
		// UninstallPackageAction::prepare()
		$package = new Package($packageID);
		if (!$package->packageID || !$package->canUninstall()) {
			$this->error('invalidUninstallation');
		}
		
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// create queue
		$queue = PackageInstallationQueueEditor::create([
			'processNo' => $processNo,
			'userID' => CLIWCF::getUser()->userID,
			'packageName' => $package->getName(),
			'packageID' => $package->packageID,
			'action' => 'uninstall'
		]);
		
		// initialize uninstallation
		$installation = new PackageUninstallationDispatcher($queue);
		
		$installation->nodeBuilder->purgeNodes();
		$installation->nodeBuilder->buildNodes();
		
		CLIWCF::getTPL()->assign([
			'queue' => $queue
		]);
		
		$queueID = $installation->nodeBuilder->getQueueByNode($queue->processNo, $installation->nodeBuilder->getNextNode());
		$step = 'uninstall';
		$node = $installation->nodeBuilder->getNextNode();
		$currentAction = CLIWCF::getLanguage()->get('wcf.package.installation.step.uninstalling');
		$progress = 0;
		
		// initialize progressbar
		$progressbar = new ProgressBar(new ConsoleProgressBar([
			'width' => CLIWCF::getTerminal()->getWidth(),
			'elements' => [
				ConsoleProgressBar::ELEMENT_PERCENT,
				ConsoleProgressBar::ELEMENT_BAR,
				ConsoleProgressBar::ELEMENT_TEXT
			],
			'textWidth' => min(floor(CLIWCF::getTerminal()->getWidth() / 2), 50)
		]));
		
		// InstallPackageAction::readParameters()
		$finished = false;
		while (!$finished) {
			$queue = new PackageInstallationQueue($queueID);
			$installation = new PackageUninstallationDispatcher($queue);
			
			switch ($step) {
				case 'uninstall':
					$_node = $installation->uninstall($node);
					
					if ($_node == '') {
						// remove node data
						$installation->nodeBuilder->purgeNodes();
						// UninstallPackageAction::finalize()
						CacheHandler::getInstance()->flushAll();
						// /UninstallPackageAction::finalize()
						
						// show success
						$currentAction = CLIWCF::getLanguage()->get('wcf.acp.package.uninstallation.step.success');
						$progress = 100;
						$step = 'success';
						$finished = true;
						continue;
					}
					
					// continue with next node
					$queueID = $installation->nodeBuilder->getQueueByNode($installation->queue->processNo, $installation->nodeBuilder->getNextNode($node));
					$step = 'uninstall';
					$progress = $installation->nodeBuilder->calculateProgress($node);
					$node = $_node;
			}
			
			$progressbar->update($progress, $currentAction);
		}
		
		$progressbar->getAdapter()->finish();
	}
	
	/**
	 * Displays an error message.
	 * 
	 * @param	string	$name
	 * @param	array	$parameters
	 */
	public function error($name, array $parameters = []) {
		Log::error('package.'.$name.':'.JSON::encode($parameters));
		
		if ($parameters) {
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.acp.package.error.'.$name, $parameters), $this->getUsage());
		}
		else {
			throw new ArgvException(CLIWCF::getLanguage()->get('wcf.acp.package.error.'.$name), $this->argv->getUsageMessage());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsage() {
		return str_replace($_SERVER['argv'][0].' [ options ]', 'package [ options ] <install|uninstall> <package>', $this->argv->getUsageMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return CLIWCF::getSession()->getPermission('admin.configuration.package.canInstallPackage') || CLIWCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage');
	}
}

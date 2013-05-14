<?php
namespace wcf\system\cli\command;
use phpline\internal\Log;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\CLIWCF;
use wcf\util\CLIUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\ProgressBar\Adapter\Console as ConsoleProgressBar;
use Zend\ProgressBar\ProgressBar;

/**
 * Executes package installation.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class PackageCommand implements ICommand {
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute(array $parameters) {
		$argv = new ArgvParser(array(
			'assumeYes|y' => 'Assumes yes',
			'assumeNo|n' => 'Assume no'
		));
		$argv->setArguments($parameters);
		$argv->parse();
		
		if (count($argv->getRemainingArgs()) !== 2) {
			throw new ArgvException('', $this->fixUsage($argv->getUsageMessage()));
		}
		if ($argv->assumeYes && $argv->assumeNo) {
			throw new ArgvException('At most one of assumes may be set', $this->fixUsage($argv->getUsageMessage()));
		}
		
		list($action, $file) = $argv->getRemainingArgs();
		CLIWCF::getReader()->setHistoryEnabled(false);
		$package = null;
		switch ($action) {
			case 'update':
				do {
					$line = CLIWCF::getReader()->readLine('package to update>');
					
					if ($line === null) return;
					$line = StringUtil::trim($line);
					if ($line == '') return;
					$sql = "SELECT	*
						FROM	wcf".WCF_N."_package
						WHERE	packageName = ?";
					$statement = CLIWCF::getDB()->prepareStatement($sql);
					$statement->execute(array($line));
					$package = $statement->fetchObject('wcf\data\package\Package');
					if ($package === null) {
						Log::error('Unknown package '.$line);
					}
				}
				while ($package === null);
			case 'install':
			break;
			default:
				throw new ArgvException('Valid actions are install and update', $this->fixUsage($argv->getUsageMessage()));
			break;
		}
		
		// PackageStartInstallForm::validateDownloadPackage()
		if (FileUtil::isURL($file)) {
			// download package
			$archive = new PackageArchive($file, $package);
			
			try {
				if (VERBOSITY >= 0) Log::info("Downloading '".$file."'");
				$file = $archive->downloadArchive();
			}
			catch (SystemException $e) {
				throw new UserInputException('', 'notFound');
			}
		}
		else {
			// probably local path
			if (!file_exists($file)) {
				throw new UserInputException('', 'notFound');
			}
			
			$archive = new PackageArchive($file, $package);
		}
		
		if (VERBOSITY >= 1) Log::debug("Starting '".$action."'");
		
		// PackageStartInstallForm::validateArchive()
		$archive->openArchive();
		$errors = PackageInstallationDispatcher::validatePHPRequirements($archive->getPhpRequirements());
		if (!empty($errors)) {
			// TODO: Nice output
			var_dump($errors);
			return;
		}
		
		if ($package !== null) {
			if (!$archive->isValidUpdate()) {
				throw new UserInputException('', 'noValidUpdate');
			}
		}
		else {
			if (!$archive->isValidInstall()) {
				throw new UserInputException('', 'noValidInstall');
			}
			else if ($archive->isAlreadyInstalled()) {
				throw new UserInputException('', 'uniqueAlreadyInstalled');
			}
		}
		
		// PackageStartInstallForm::save()
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// insert queue
		$queue = PackageInstallationQueueEditor::create(array(
			'processNo' => $processNo,
			'userID' => CLIWCF::getUser()->userID,
			'package' => $archive->getPackageInfo('name'),
			'packageName' => $archive->getLocalizedPackageInfo('packageName'),
			'packageID' => ($package && $package->packageID) ? $package->packageID : null,
			'archive' => $file,
			'action' => $action,
			'confirmInstallation' => 1
		));
		
		// PackageInstallationDispatcher::openQueue()
		$parentQueueID = 0;
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", array(CLIWCF::getUser()->userID));
		$conditions->add("parentQueueID = ?", array($parentQueueID));
		if ($processNo != 0) $conditions->add("processNo = ?", array($processNo));
		$conditions->add("done = ?", array(0));
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_queue
			".$conditions."
			ORDER BY	queueID ASC";
		$statement = CLIWCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageInstallation = $statement->fetchArray();
		if (!isset($packageInstallation['queueID'])) {
			// todo: what to output?
			echo "PackageInstallationDispatcher::openQueue()";
			return;
		}
		else {
			$queueID = $packageInstallation['queueID'];
		}
		
		// PackageInstallationConfirmPage::readParameters()
		$queue = new PackageInstallationQueue($queueID);
		if (!$queue->queueID || $queue->done) {
			// todo: what to output?
			echo "PackageInstallationConfirmPage::readParameters()";
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
					if ($openRequirements[$requirement['name']]['action'] === 'update') {
						$requirement['status'] = 'missingVersion';
						$requirement['existingVersion'] = $openRequirements[$requirement['name']]['existingVersion'];
					}
					$missingPackages++;
				}
				else {
					$requirement['status'] = 'delivered';
				}
			}
			else {
				$requirement['status'] = 'installed';
			}
		}
		unset($requirement);
		
		// PackageInstallationConfirmPage::assignVariables/show()
		$tplData = array(
			'archive' => $packageInstallationDispatcher->getArchive(),
			'requiredPackages' => $requirements,
			'missingPackages' => $missingPackages,
			'excludingPackages' => $packageInstallationDispatcher->getArchive()->getConflictedExcludingPackages(),
			'excludedPackages' => $packageInstallationDispatcher->getArchive()->getConflictedExcludedPackages(),
			'queueID' => $queue->queueID
		);
		$table = array(
			array(CLIWCF::getLanguage()->get('wcf.acp.package.identifier'), $tplData['archive']->getPackageInfo('name')),
			array(CLIWCF::getLanguage()->get('wcf.acp.package.version'), $tplData['archive']->getPackageInfo('version')),
			array(CLIWCF::getLanguage()->get('wcf.acp.package.packageDate'), CLIUtil::formatDate($tplData['archive']->getPackageInfo('date'))),
			array(CLIWCF::getLanguage()->get('wcf.acp.package.author'), $tplData['archive']->getAuthorInfo('author')),
		);
		CLIWCF::getReader()->println(CLIUtil::generateTable($table));
		
		$list = $tplData['excludingPackages'];
		if (count($list)) {
			Log::error(CLIWCF::getLanguage()->get('wcf.acp.package.install.error.excludingPackages'));
			CLIWCF::getReader()->println(CLIUtil::generateList($list));
		}
		
		$list = $tplData['excludedPackages'];
		if (count($list)) {
			Log::error(CLIWCF::getLanguage()->get('wcf.acp.package.install.error.excludedPackages'));
			CLIWCF::getReader()->println(CLIUtil::generateList($list));
		}
		
		Log::info(CLIWCF::getLanguage()->get('wcf.acp.package.dependencies.required').' ('.StringUtil::formatInteger(count($tplData['requiredPackages'])).')');
		$table = array(array(
			CLIWCF::getLanguage()->get('wcf.acp.package.name'),
			CLIWCF::getLanguage()->get('wcf.acp.package.installation.packageStatus'),
			CLIWCF::getLanguage()->get('wcf.acp.package.installation.requiredVersion')
		));
		foreach ($tplData['requiredPackages'] as $package) {
			switch ($package['status']) {
				case 'installed':
					$color = Color::GREEN;
				break;
				case 'delivered':
					$color = Color::YELLOW;
				break;
				default:
					$color = Color::RED;
				break;
			}
			$minVersion = '';
			if (isset($package['minversion'])) {
				if ($package['status'] == 'missingVersion') {
					$minVersion = CLIUtil::colorize($package['minversion'], Color::RED);
				}
				else {
					$minVersion = $package['minversion'];
				}
			}
			$row = array(
				CLIUtil::colorize($package['name'], $color),
				CLIWCF::getLanguage()->get('wcf.acp.package.installation.packageStatus.'.$package['status']),
				$minVersion
			);
			
			$table[] = $row;
		}
		CLIWCF::getReader()->println(CLIUtil::generateTable($table));
		
		if (!($tplData['missingPackages'] == 0 && count($tplData['excludingPackages']) == 0 && count($tplData['excludedPackages']) == 0)) {
			// todo: what to output?
			Log::error("Unable to continue");
			return;
		}
		
		if (!$argv->assumeYes) {
			// todo: replace by proper form
			$line = CLIWCF::getReader()->readLine('confirm>');
			$line = StringUtil::trim($line);
			if ($line != 'yes') return;
		}
		
		switch ($action) {
			case 'install':
			case 'update':
				// AbstractDialogAction::readParameters()
				$step = 'prepare';
				$queueID = $queue->queueID;
				$node = '';
				
				// initialize progressbar
				$progressbar = new ProgressBar(new ConsoleProgressBar(array(
					'width' => CLIWCF::getTerminal()->getWidth()
				)));
				
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
						break;
						
						case 'install':
							// InstallPackageAction::stepInstall()
							$step_ = $installation->install($node);
							$queueID = $installation->nodeBuilder->getQueueByNode($installation->queue->processNo, $step_->getNode());
							
							if ($step_->hasDocument()) {
								$innerTemplate = $step_->getTemplate();
								$progress = $installation->nodeBuilder->calculateProgress($node);
								$node = $step_->getNode();
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
									$finished = true;
									continue;
								}
								else {
									// continue with next node
									$progress = $installation->nodeBuilder->calculateProgress($node);
									$node = $step_->getNode();
								}
							}
						break;
					}
					
					$progressbar->update($progress);
				}
			break;
		}
	}
	
	public function fixUsage($usage) {
		return str_replace($_SERVER['argv'][0].' [ options ]', $_SERVER['argv'][0].' [ options ] <install|update> <package>', $usage);
	}
	
	/**
	 * @see \wcf\system\cli\command\ICommand::canAccess()
	 */
	public function canAccess() {
		return CLIWCF::getSession()->getPermission('admin.system.package.canInstallPackage') || CLIWCF::getSession()->getPermission('admin.system.package.canUpdatePackage');
	}
}

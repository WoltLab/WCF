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
use wcf\util\JSON;
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
	private $argv = null;
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute(array $parameters) {
		$this->argv = new ArgvParser(array());
		
		if (count($parameters) !== 2) {
			throw new ArgvException('', $this->fixUsage($this->argv->getUsageMessage()));
		}
		
		list($action, $file) = $parameters;
		CLIWCF::getReader()->setHistoryEnabled(false);
		$package = null;
		switch ($action) {
			case 'update':
				$line = CLIWCF::getReader()->readLine(CLIWCF::getLanguage()->get('wcf.cli.command.package.updatePackage'));
				
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
					$this->error('unknownPackage', array('package' => $line));
				}
			case 'install':
			break;
			default:
				$this->error('invalidAction', array('action' => $action));
			break;
		}
		
		// PackageStartInstallForm::validateDownloadPackage()
		if (FileUtil::isURL($file)) {
			// download package
			$archive = new PackageArchive($file, $package);
			
			try {
				if (VERBOSITY >= 1) Log::info("Downloading '".$file."'");
				$file = $archive->downloadArchive();
			}
			catch (SystemException $e) {
				$this->error('notFound', array('file' => $file));
			}
		}
		else {
			// probably local path
			if (!file_exists($file)) {
				$this->error('notFound', array('file' => $file));
			}
			
			$archive = new PackageArchive($file, $package);
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
			$this->error('phpRequirements', array('errors' => $errors));
		}
		
		if ($package !== null) {
			if (!$archive->isValidUpdate()) {
				$this->error('noValidUpdate');
			}
		}
		else {
			if (!$archive->isValidInstall()) {
				$this->error('noValidInstall');
			}
			else if ($archive->getPackageInfo('isApplication')) {
				$this->error('installIsApplication');
			}
			else if ($archive->isAlreadyInstalled()) {
				$this->error('uniqueAlreadyInstalled');
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
			Log::error('internalOpenQueue');
			return;
		}
		else {
			$queueID = $packageInstallation['queueID'];
		}
		
		// PackageInstallationConfirmPage::readParameters()
		$queue = new PackageInstallationQueue($queueID);
		if (!$queue->queueID || $queue->done) {
			// todo: what to output?
			Log::error('internalReadParameters');
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
		$excludingPackages = $packageInstallationDispatcher->getArchive()->getConflictedExcludingPackages();
		$excludedPackages = $packageInstallationDispatcher->getArchive()->getConflictedExcludedPackages();
		if (!($missingPackages == 0 && count($excludingPackages) == 0 && count($excludedPackages) == 0)) {
			$this->error('missingPackagesOrExclude', array(
				'requirements' => $requirements,
				'excludingPackages' => $excludingPackages,
				'excludedPackages' => $excludedPackages
			));
			return;
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
					'width' => CLIWCF::getTerminal()->getWidth(),
					'elements' => array(
						ConsoleProgressBar::ELEMENT_PERCENT,
						ConsoleProgressBar::ELEMENT_BAR,
						ConsoleProgressBar::ELEMENT_TEXT
					),
					'textWidth' => min(floor(CLIWCF::getTerminal()->getWidth() / 2), 50)
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
							$currentAction = $installation->nodeBuilder->getPackageNameByQueue($queueID);
						break;
						
						case 'install':
							// InstallPackageAction::stepInstall()
							$step_ = $installation->install($node);
							$queueID = $installation->nodeBuilder->getQueueByNode($installation->queue->processNo, $step_->getNode());
							
							if ($step_->hasDocument()) {
								$innerTemplate = $step_->getTemplate();
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
			break;
		}
	}
	
	public function error($name, array $parameters = array()) {
		Log::error('package.'.$name.':'.JSON::encode($parameters));
		
		if ($parameters) {
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.acp.package.error.'.$name, $parameters), $this->fixUsage($this->argv->getUsageMessage()));
		}
		else {
			throw new ArgvException(CLIWCF::getLanguage()->get('wcf.acp.package.error.'.$name), $this->fixUsage($this->argv->getUsageMessage()));
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

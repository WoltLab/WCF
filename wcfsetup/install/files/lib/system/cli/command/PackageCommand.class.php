<?php
namespace wcf\system\cli\command;
use phpline\internal\Log;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
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
		$this->argv->setArguments($parameters);
		$this->argv->parse();
		
		if (count($this->argv->getRemainingArgs()) !== 2) {
			throw new ArgvException('', $this->fixUsage($this->argv->getUsageMessage()));
		}
		
		list($action, $file) = $this->argv->getRemainingArgs();
		CLIWCF::getReader()->setHistoryEnabled(false);
		
		if ($action !== 'install') throw new ArgvException('', $this->fixUsage($this->argv->getUsageMessage()));
		
		// PackageStartInstallForm::validateDownloadPackage()
		if (FileUtil::isURL($file)) {
			// download package
			$archive = new PackageArchive($file, null);
			
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
			$this->error('phpRequirements', array('errors' => $errors));
		}
		
		// try to find existing package
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = CLIWCF::getDB()->prepareStatement($sql);
		$statement->execute(array($archive->getPackageInfo('name')));
		$row = $statement->fetchArray();
		$package = null;
		if ($row !== false) {
			$package = new Package(null, $row);
		}
		
		// check update or install support
		if ($package !== null) {
			CLIWCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
			
			$archive->setPackage($package);
			if (!$archive->isValidUpdate()) {
				$this->error('noValidUpdate');
			}
		}
		else {
			CLIWCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
			
			if (!$archive->isValidInstall()) {
				$this->error('noValidInstall');
			}
			else if ($archive->getPackageInfo('isApplication')) {
				$this->error('installIsApplication');
			}
			else if ($archive->isAlreadyInstalled()) {
				$this->error('uniqueAlreadyInstalled');
			}
			else if ($archive->getPackageInfo('isApplication') && $this->archive->hasUniqueAbbreviation()) {
				$this->error('noUniqueAbbrevation');
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
			'packageID' => ($package !== null) ? $package->packageID : null,
			'archive' => $file,
			'action' => ($package !== null ? 'update' : 'install'),
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
		
		switch ('install') {
			case 'install':
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
		return str_replace($_SERVER['argv'][0].' [ options ]', $_SERVER['argv'][0].' [ options ] install <package>', $usage);
	}
	
	/**
	 * @see \wcf\system\cli\command\ICommand::canAccess()
	 */
	public function canAccess() {
		return CLIWCF::getSession()->getPermission('admin.system.package.canInstallPackage') || CLIWCF::getSession()->getPermission('admin.system.package.canUpdatePackage');
	}
}

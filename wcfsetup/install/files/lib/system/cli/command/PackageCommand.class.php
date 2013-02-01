<?php
namespace wcf\system\cli\command;
use phpline\internal\Log;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\CLIWCF;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

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
			'assumeYes' => 'Assumes yes',
			'assumeNo' => 'Assume no'
		));
		$argv->setArguments($parameters);
		$argv->parse();
		
		if (count($argv->getRemainingArgs()) !== 2) {
			throw new ArgvException('', $this->fixUsage($argv->getUsageMessage()));
		}
		
		list($action, $file) = $argv->getRemainingArgs();
		
		$package = null;
		switch ($action) {
			case 'update':
				do {
					CLIWCF::getReader()->setHistoryEnabled(false);
					$line = CLIWCF::getReader()->readLine('package to update>');
					CLIWCF::getReader()->setHistoryEnabled(true);
					
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
		
		$archive = new PackageArchive($file, $package);
		$archive->openArchive();
		$errors = PackageInstallationDispatcher::validatePHPRequirements($archive->getPhpRequirements());
		if (!empty($errors)) {
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
		
		echo $processNo;
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

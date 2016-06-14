<?php
namespace wcf\system\package;
use wcf\data\option\OptionEditor;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\PackageCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\setup\Uninstaller;
use wcf\system\style\StyleHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Handles the whole uninstallation process.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageUninstallationDispatcher extends PackageInstallationDispatcher {
	/**
	 * is true if the package's uninstall script has been executed or if no
	 * such script exists
	 * @var	boolean
	 */
	protected $didExecuteUninstallScript = false;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Creates a new instance of PackageUninstallationDispatcher.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 */
	public function __construct(PackageInstallationQueue $queue) {
		$this->queue = $queue;
		$this->nodeBuilder = new PackageUninstallationNodeBuilder($this);
		
		$this->action = $this->queue->action;
	}
	
	/**
	 * Uninstalls node components and returns next node.
	 * 
	 * @param	string		$node
	 * @return	string
	 */
	public function uninstall($node) {
		$nodes = $this->nodeBuilder->getNodeData($node);
		
		// invoke node-specific actions
		foreach ($nodes as $data) {
			$nodeData = unserialize($data['nodeData']);
			
			switch ($data['nodeType']) {
				case 'package':
					$this->uninstallPackage($nodeData);
				break;
				
				case 'pip':
					// the file pip is always executed last, thus, just before it,
					// execute the uninstall script
					if ($nodeData['pluginName'] == 'file' && !$this->didExecuteUninstallScript) {
						$this->executeUninstallScript();
						
						$this->didExecuteUninstallScript = true;
					}
					
					$this->executePIP($nodeData);
				break;
			}
		}
		
		// mark node as completed
		$this->nodeBuilder->completeNode($node);
		$node = $this->nodeBuilder->getNextNode($node);
		
		// perform post-uninstall actions
		if ($node == '') {
			// update options.inc.php if uninstallation is completed
			OptionEditor::resetCache();
			
			// clear cache
			CacheHandler::getInstance()->flushAll();
			
			// reset language cache
			LanguageFactory::getInstance()->clearCache();
			LanguageFactory::getInstance()->deleteLanguageCache();
			
			// reset stylesheets
			StyleHandler::resetStylesheets();
			
			// rebuild application paths
			ApplicationHandler::rebuild();
			
			// clear user storage
			UserStorageHandler::getInstance()->clear();
			
			EventHandler::getInstance()->fireAction($this, 'postUninstall');
		}
		
		// return next node
		return $node;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function executePIP(array $nodeData) {
		/** @var IPackageInstallationPlugin $pip */
		$pip = new $nodeData['className']($this);
		
		$pip->uninstall();
	}
	
	/**
	 * Executes the package's uninstall script (if existing).
	 * 
	 * @since	3.0
	 */
	protected function executeUninstallScript() {
		// check if uninstall script file for the uninstalled package exists
		$uninstallScript = WCF_DIR.'acp/uninstall/'.$this->getPackage()->package.'.php';
		if (file_exists($uninstallScript)) {
			include($uninstallScript);
		}
	}
	
	/**
	 * Uninstalls current package.
	 * 
	 * @param	array		$nodeData
	 */
	protected function uninstallPackage(array $nodeData) {
		PackageEditor::deleteAll([$this->queue->packageID]);
		
		// remove localized package infos
		// todo: license/readme
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem IN (?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			'wcf.acp.package.packageName.package'.$this->queue->packageID,
			'wcf.acp.package.packageDescription.package'.$this->queue->packageID
		]);
		
		// reset package cache
		PackageCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Deletes the given list of files from the target dir.
	 * 
	 * @param	string		$targetDir
	 * @param	string		$files
	 * @param	boolean		$deleteEmptyDirectories
	 * @param	boolean		$deleteEmptyTargetDir
	 */
	public function deleteFiles($targetDir, $files, $deleteEmptyTargetDir = false, $deleteEmptyDirectories = true) {
		new Uninstaller($targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories);
	}
	
	/**
	 * Adds an uninstall entry to the package installation queue.
	 * 
	 * @param	Package		$package
	 * @param	array		$packages
	 */
	public static function addQueueEntries(Package $package, $packages = []) {
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// add dependent packages to queue
		$statementParameters = [];
		foreach ($packages as $dependentPackage) {
			$statementParameters[] = [
				'packageName' => $dependentPackage['packageName'],
				'packageID' => $dependentPackage['packageID']
			];
		}
		
		// add uninstalling package to queue
		$statementParameters[] = [
			'packageName' => $package->getName(),
			'packageID' => $package->packageID
		];
		
		// insert queue entry (entries)
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
					(processNo, userID, package, packageID, action)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($statementParameters as $parameter) {
			$statement->execute([
				$processNo,
				WCF::getUser()->userID,
				$parameter['packageName'],
				$parameter['packageID'],
				'uninstall'
			]);
		}
		
		self::openQueue(0, $processNo);
	}
}

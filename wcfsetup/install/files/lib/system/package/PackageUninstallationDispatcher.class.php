<?php
namespace wcf\system\package;
use wcf\data\option\OptionEditor;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\data\package\PackageList;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\setup\Uninstaller;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * PackageUninstallationDispatcher handles the whole uninstallation process.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class PackageUninstallationDispatcher extends PackageInstallationDispatcher {
	/**
	 * Creates a new instance of PackageUninstallationDispatcher.
	 *
	 * @param	PackageInstallationQueue	$queue
	 */
	public function __construct(PackageInstallationQueue $queue) {
		$this->queue = $queue;
		$this->nodeBuilder = new PackageUninstallationNodeBuilder($this);
		
		$this->action = $this->queue->installationType;
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
					$this->executePIP($nodeData);
				break;
			}
		}
		
		// mark node as completed
		$this->nodeBuilder->completeNode($node);
		$node = $this->nodeBuilder->getNextNode($node);
		
		// update options.inc.php if uninstallation is completed
		if ($node == '') {
			OptionEditor::resetCache();
		}
		
		// return next node
		return $node;
	}
	
	/**
	 * @see	wcf\system\package\PackageInstallationDispatcher::executePIP()
	 */
	protected function executePIP(array $nodeData) {
		$pip = new $nodeData['className']($this);
		$pip->uninstall();
	}
	
	/**
	 * Uninstalls current package.
	 *
	 * @param	array		$nodeData
	 */
	protected function uninstallPackage(array $nodeData) {
		PackageEditor::deleteAll(array($this->queue->packageID));
		
		// remove localized package infos
		// todo: license/readme
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem IN (?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			'wcf.acp.package.packageName.package'.$this->queue->packageID,
			'wcf.acp.package.packageDescription.package'.$this->queue->packageID
		));
		
		// reset package cache
		CacheHandler::getInstance()->clearResource('packages');
		
		// rebuild package dependencies
		Package::rebuildParentPackageDependencies($this->queue->packageID);
	}
	
	/**
	 * Deletes the given list of files from the target dir.
	 *
	 * @param 	string 		$targetDir
	 * @param 	string 		$files
	 * @param	boolean		$deleteEmptyDirectories
	 * @param	booelan		$deleteEmptyTargetDir
	 */
	public function deleteFiles($targetDir, $files, $deleteEmptyTargetDir = false, $deleteEmptyDirectories = true) {
		new Uninstaller($targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories);
	}
	
	/**
	 * Checks whether this package is required by other packages.
	 * If so than a template will be displayed to warn the user that
	 * a further uninstallation will uninstall also the dependent packages
	 */
	public static function checkDependencies() {
		$packageID = 0;
		if (isset($_REQUEST['packageID'])) {
			$packageID = intval($_REQUEST['packageID']);
		}
		
		// get packages info
		try {
			// create object of uninstalling package
			$package = new Package($packageID);
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
		
		// can not uninstall wcf package.
		if ($package->package == 'com.woltlab.wcf') {
			throw new IllegalLinkException();
		}
		
		$dependentPackages = array();
		$uninstallAvailable = true;
		if ($package->isRequired()) {
			// get packages that requires this package
			$dependentPackages = self::getPackageDependencies($package->packageID);
			foreach ($dependentPackages as $dependentPackage) {
				if ($dependentPackage['packageID'] == PACKAGE_ID) {
					$uninstallAvailable = false;
					break;
				}
			}
		}
		
		// add this package to queue
		self::addQueueEntries($package, $dependentPackages);
	}
	
	/**
	 * Get all packages which require this package.
	 *
	 * @param	integer		$packageID
	 * @return	array
	 */
	public static function getPackageDependencies($packageID) {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package
			WHERE		packageID IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package_requirement_map
						WHERE	requirement = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$packages = array();
		while ($row = $statement->fetchArray()) {
			$packages[] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Returns an ordered list of depenencies for given package id. The order is
	 * curcial, whereas the first package has to be uninstalled first.
	 * 
	 * @package	integer
	 * @return	wcf\data\package\PackageList
	 */
	public static function getOrderedPackageDependencies($packageID) {
		$sql = "SELECT		packageID, MAX(level) AS level
			FROM		wcf".WCF_N."_package_requirement_map
			WHERE		requirement = ?
			GROUP BY	packageID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		
		$dependencies = array();
		while ($row = $statement->fetchArray()) {
			$dependencies[$row['packageID']] = $row['level'];
		}
		
		$packageIDs = array();
		$maxLevel = max(array_values($dependencies));
		if ($maxLevel == 0) {
			// order does not matter
			$packageIDs = array_keys($dependencies);
		}
		else {
			// order by level while ignoring individual connections as they don't
			// matter if uninstall begins with the lowest dependency in tree
			for ($i = $maxLevel; $i >= 0; $i--) {
				foreach ($dependencies as $packageID => $level) {
					if ($level == $i) {
						$packageIDs[] = $packageID;
						unset($dependencies[$packageID]);
					}
				}
			}
		}
		
		// get packages
		$packageList = new PackageList();
		$packageList->sqlLimit = 0;
		$packageList->getConditionBuilder()->add("packageID IN (?)", array($packageIDs));
		$packageList->readObjects();
		
		return $packageList;
	}
	
	/**
	 * Returns true if package has dependencies
	 *
	 * @param	integer		$packageID
	 * @return	boolean
	 */
	public static function hasDependencies($packageID) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_requirement
			WHERE	requirement = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$row = $statement->fetchArray();
		
		return ($row['count'] > 0);
	}
	
	/**
	 * Adds an uninstall entry to the package installation queue.
	 *
	 * @param	Package		$package
	 * @param	array		$packages
	 */
	public static function addQueueEntries(Package $package, $packages = array()) {
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// add dependent packages to queue
		$statementParameters = array();
		foreach ($packages as $dependentPackage) {
			$statementParameters[] = array(
				'packageName' => $dependentPackage['packageName'],
				'packageID' => $dependentPackage['packageID']
			);
		}
		
		// add uninstalling package to queue
		$statementParameters[] = array(
			'packageName' => $package->getName(),
			'packageID' => $package->packageID
		);
		
		// insert queue entry (entries)
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
					(processNo, userID, package, packageID, action)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($statementParameters as $parameter) {
			$statement->execute(array(
				$processNo,
				WCF::getUser()->userID,
				$parameter['packageName'],
				$parameter['packageID'],
				'uninstall'
			));
		}
		
		$url = LinkHandler::getInstance()->getLink('Package', array(), 'action=openQueue&processNo='.$processNo);
		HeaderUtil::redirect($url);
		exit;
	}
}

<?php
namespace wcf\system\package;
use wcf\data\application\Application;
use wcf\data\application\ApplicationEditor;
use wcf\data\devtools\project\DevtoolsProjectAction;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\LanguageEditor;
use wcf\data\language\LanguageList;
use wcf\data\option\OptionEditor;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\TemplateListenerCodeCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\DevtoolsSetup;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\form\container\GroupFormElementContainer;
use wcf\system\form\container\MultipleSelectionFormElementContainer;
use wcf\system\form\element\MultipleSelectionFormElement;
use wcf\system\form\element\TextInputFormElement;
use wcf\system\form\FormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\setup\IFileHandler;
use wcf\system\setup\Installer;
use wcf\system\style\StyleHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * PackageInstallationDispatcher handles the whole installation process.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageInstallationDispatcher {
	/**
	 * current installation type
	 * @var	string
	 */
	protected $action = '';
	
	/**
	 * instance of PackageArchive
	 * @var	PackageArchive
	 */
	public $archive;
	
	/**
	 * instance of PackageInstallationNodeBuilder
	 * @var	PackageInstallationNodeBuilder
	 */
	public $nodeBuilder;
	
	/**
	 * instance of Package
	 * @var	Package
	 */
	public $package;
	
	/**
	 * instance of PackageInstallationQueue
	 * @var	PackageInstallationQueue
	 */
	public $queue;
	
	/**
	 * default name of the config file
	 * @var	string
	 */
	const CONFIG_FILE = 'app.config.inc.php';
	
	/**
	 * data of previous package in queue
	 * @var	string[]
	 */
	protected $previousPackageData;
	
	/**
	 * Creates a new instance of PackageInstallationDispatcher.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 */
	public function __construct(PackageInstallationQueue $queue) {
		$this->queue = $queue;
		$this->nodeBuilder = new PackageInstallationNodeBuilder($this);
		
		$this->action = $this->queue->action;
	}
	
	/**
	 * Sets data of previous package in queue.
	 * 
	 * @param	string[]	$packageData
	 */
	public function setPreviousPackage(array $packageData) {
		$this->previousPackageData = $packageData;
	}
	
	/**
	 * Installs node components and returns next node.
	 * 
	 * @param	string		$node
	 * @return	PackageInstallationStep
	 * @throws      SystemException
	 */
	public function install($node) {
		$nodes = $this->nodeBuilder->getNodeData($node);
		if (empty($nodes)) {
			// guard against possible issues with empty instruction blocks, including
			// these blocks that contain no valid instructions at all (e.g. typo from
			// copy & paste)
			throw new SystemException("Failed to retrieve nodes for identifier '".$node."', the query returned no results.");
		}
		
		// invoke node-specific actions
		$step = null;
		foreach ($nodes as $data) {
			$nodeData = unserialize($data['nodeData']);
			$this->logInstallationStep($data);
			
			switch ($data['nodeType']) {
				case 'package':
					$step = $this->installPackage($nodeData);
				break;
				
				case 'pip':
					$step = $this->executePIP($nodeData);
				break;
				
				case 'optionalPackages':
					$step = $this->selectOptionalPackages($node, $nodeData);
				break;
				
				default:
					die("Unknown node type: '".$data['nodeType']."'");
				break;
			}
			
			if ($step->splitNode()) {
				$log = 'split node';
				if ($step->getException() !== null && $step->getException()->getMessage()) {
					$log .= ': ' . $step->getException()->getMessage();
				}
				
				$this->logInstallationStep($data, $log);
				$this->nodeBuilder->cloneNode($node, $data['sequenceNo']);
				break;
			}
		}
		
		// mark node as completed
		$this->nodeBuilder->completeNode($node);
		
		// assign next node
		$node = $this->nodeBuilder->getNextNode($node);
		$step->setNode($node);
		
		// perform post-install/update actions
		if ($node == '') {
			$this->logInstallationStep([], 'start cleanup');
			
			// update "last update time" option
			$sql = "UPDATE	wcf".WCF_N."_option
				SET	optionValue = ?
				WHERE	optionName = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				TIME_NOW,
				'last_update_time'
			]);
			
			// update options.inc.php
			OptionEditor::resetCache();
			
			if ($this->action == 'install') {
				// save localized package infos
				$this->saveLocalizedPackageInfos();
				
				// remove all cache files after WCFSetup
				if (!PACKAGE_ID) {
					CacheHandler::getInstance()->flushAll();
					
					$sql = "UPDATE	wcf".WCF_N."_option
						SET	optionValue = ?
						WHERE	optionName = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					
					$statement->execute([
						StringUtil::getUUID(),
						'wcf_uuid'
					]);
					
					if (file_exists(WCF_DIR . 'cookiePrefix.txt')) {
						$statement->execute([
							COOKIE_PREFIX,
							'cookie_prefix'
						]);
						
						@unlink(WCF_DIR . 'cookiePrefix.txt');
					}
					
					$user = new User(1);
					$statement->execute([
						$user->username,
						'mail_from_name'
					]);
					$statement->execute([
						$user->email,
						'mail_from_address'
					]);
					$statement->execute([
						$user->email,
						'mail_admin_address'
					]);
					
					try {
						$statement->execute([
							bin2hex(\random_bytes(20)),
							'signature_secret'
						]);
					}
					catch (\Throwable $e) {
						// ignore, the secret will stay empty and crypto operations
						// depending on it will fail
					}
					
					if (WCF::getSession()->getVar('__wcfSetup_developerMode')) {
						$statement->execute([
							1,
							'enable_debug_mode'
						]);
						$statement->execute([
							'public',
							'exception_privacy'
						]);
						$statement->execute([
							'debugFolder',
							'mail_send_method'
						]);
						$statement->execute([
							1,
							'enable_developer_tools'
						]);
						$statement->execute([
							1,
							'log_missing_language_items'
						]);
						
						foreach (DevtoolsSetup::getInstance()->getOptionOverrides() as $optionName => $optionValue) {
							$statement->execute([
								$optionValue,
								$optionName
							]);
						}
						
						foreach (DevtoolsSetup::getInstance()->getUsers() as $newUser) {
							try {
								(new UserAction([], 'create', [
									'data' => [
										'email' => $newUser['email'],
										'password' => $newUser['password'],
										'username' => $newUser['username']
									],
									'groups' => [
										1,
										3
									]
								]))->executeAction();
							}
							catch (SystemException $e) {
								// ignore errors due to event listeners missing at this
								// point during installation
							}
						}
						
						if (($importPath = DevtoolsSetup::getInstance()->getDevtoolsImportPath()) !== '') {
							(new DevtoolsProjectAction([], 'quickSetup', [
								'path' => $importPath
							]))->executeAction();
						}
					}
					
					if (WCF::getSession()->getVar('__wcfSetup_imagick')) {
						$statement->execute([
							'imagick',
							'image_adapter_type',
						]);
					}
					
					// update options.inc.php
					OptionEditor::resetCache();
					
					WCF::getSession()->register('__wcfSetup_completed', true);
				}
				
				// rebuild application paths
				ApplicationHandler::rebuild();
			}
			
			// remove template listener cache
			TemplateListenerCodeCacheBuilder::getInstance()->reset();
			
			// reset language cache
			LanguageFactory::getInstance()->clearCache();
			LanguageFactory::getInstance()->deleteLanguageCache();
			
			// reset stylesheets
			StyleHandler::resetStylesheets();
			
			// clear user storage
			UserStorageHandler::getInstance()->clear();
			
			// rebuild config files for affected applications
			$sql = "SELECT		package.packageID
				FROM		wcf".WCF_N."_package_installation_queue queue,
						wcf".WCF_N."_package package
				WHERE		queue.processNo = ?
						AND package.packageID = queue.packageID
						AND package.isApplication = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$this->queue->processNo,
				1
			]);
			while ($row = $statement->fetchArray()) {
				Package::writeConfigFile($row['packageID']);
			}
			
			EventHandler::getInstance()->fireAction($this, 'postInstall');
			
			// remove archives
			$sql = "SELECT	archive
				FROM	wcf".WCF_N."_package_installation_queue
				WHERE	processNo = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->queue->processNo]);
			while ($row = $statement->fetchArray()) {
				@unlink($row['archive']);
			}
			
			// delete queues
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
				WHERE		processNo = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->queue->processNo]);
			
			$this->logInstallationStep([], 'finished cleanup');
		}
		
		return $step;
	}
	
	/**
	 * Logs an installation step.
	 * 
	 * @param	array		$node	data of the executed node
	 * @param	string		$log	optional additional log text
	 */
	protected function logInstallationStep(array $node = [], $log = '') {
		$logEntry = "[" . TIME_NOW . "]\n";
		if (!empty($node)) {
			$logEntry .= 'sequenceNo: ' . $node['sequenceNo'] . "\n";
			$logEntry .= 'nodeType: ' . $node['nodeType'] . "\n";
			$logEntry .= "nodeData:\n";
			
			$nodeData = unserialize($node['nodeData']);
			foreach ($nodeData as $index => $value) {
				$logEntry .= "\t" . $index . ': ' . (!is_object($value) && !is_array($value) ? $value : JSON::encode($value)) . "\n";
			}
		}
		
		if ($log) {
			$logEntry .= 'additional information: ' . $log . "\n";
		}
		
		$logEntry .= str_repeat('-', 30) . "\n\n";
		
		file_put_contents(
			WCF_DIR . 'log/' . date('Y-m-d', TIME_NOW) . '-update-' . $this->queue->queueID . '.txt',
			$logEntry,
			FILE_APPEND
		);
	}
	
	/**
	 * Returns current package archive.
	 * 
	 * @return	PackageArchive
	 */
	public function getArchive() {
		if ($this->archive === null) {
			// check if we're doing an iterative update of the same package
			if ($this->previousPackageData !== null && $this->getPackage()->package == $this->previousPackageData['package']) {
				if (Package::compareVersion($this->getPackage()->packageVersion, $this->previousPackageData['packageVersion'], '<')) {
					// fake package to simulate the package version required by current archive
					$this->getPackage()->setPackageVersion($this->previousPackageData['packageVersion']);
				}
			}
			
			$this->archive = new PackageArchive($this->queue->archive, $this->getPackage());
			
			if (FileUtil::isURL($this->archive->getArchive())) {
				// get return value and update entry in
				// package_installation_queue with this value
				$archive = $this->archive->downloadArchive();
				$queueEditor = new PackageInstallationQueueEditor($this->queue);
				$queueEditor->update(['archive' => $archive]);
			}
			
			$this->archive->openArchive();
		}
		
		return $this->archive;
	}
	
	/**
	 * Installs current package.
	 * 
	 * @param	mixed[]		$nodeData
	 * @return	PackageInstallationStep
	 * @throws	SystemException
	 */
	protected function installPackage(array $nodeData) {
		$installationStep = new PackageInstallationStep();
		
		// check requirements
		if (!empty($nodeData['requirements'])) {
			foreach ($nodeData['requirements'] as $package => $requirementData) {
				// get existing package
				if ($requirementData['packageID']) {
					$sql = "SELECT	packageName, packageVersion
						FROM	wcf".WCF_N."_package
						WHERE	packageID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$requirementData['packageID']]);
				}
				else {
					// try to find matching package
					$sql = "SELECT	packageName, packageVersion
						FROM	wcf".WCF_N."_package
						WHERE	package = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$package]);
				}
				$row = $statement->fetchArray();
				
				// package is required but not available
				if ($row === false) {
					throw new SystemException("Package '".$package."' is required by '".$nodeData['packageName']."', but is neither installed nor shipped.");
				}
				
				// check version requirements
				if ($requirementData['minVersion']) {
					if (Package::compareVersion($row['packageVersion'], $requirementData['minVersion']) < 0) {
						throw new SystemException("Package '".$nodeData['packageName']."' requires package '".$row['packageName']."' in version '".$requirementData['minVersion']."', but only version '".$row['packageVersion']."' is installed");
					}
				}
			}
		}
		unset($nodeData['requirements']);
		
		$applicationDirectory = '';
		if (isset($nodeData['applicationDirectory'])) {
			$applicationDirectory = $nodeData['applicationDirectory'];
			unset($nodeData['applicationDirectory']);
		}
		
		// update package
		if ($this->queue->packageID) {
			$packageEditor = new PackageEditor(new Package($this->queue->packageID));
			unset($nodeData['installDate']);
			$packageEditor->update($nodeData);
			
			// delete old excluded packages
			$sql = "DELETE FROM	wcf".WCF_N."_package_exclusion
				WHERE		packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->queue->packageID]);
			
			// delete old compatibility versions
			$sql = "DELETE FROM	wcf".WCF_N."_package_compatibility
				WHERE		packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->queue->packageID]);
			
			// delete old requirements and dependencies
			$sql = "DELETE FROM	wcf".WCF_N."_package_requirement
				WHERE		packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->queue->packageID]);
		}
		else {
			// create package entry
			$package = $this->createPackage($nodeData);
			
			// update package id for current queue
			$queueEditor = new PackageInstallationQueueEditor($this->queue);
			$queueEditor->update(['packageID' => $package->packageID]);
			
			// reload queue
			$this->queue = new PackageInstallationQueue($this->queue->queueID);
			$this->package = null;
			
			if ($package->isApplication) {
				$host = str_replace(RouteHandler::getProtocol(), '', RouteHandler::getHost());
				$path = RouteHandler::getPath(['acp']);
				
				// insert as application
				ApplicationEditor::create([
					'domainName' => $host,
					'domainPath' => $path,
					'cookieDomain' => $host,
					'packageID' => $package->packageID
				]);
			}
		}
		
		// save excluded packages
		if (count($this->getArchive()->getExcludedPackages())) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_exclusion
						(packageID, excludedPackage, excludedPackageVersion)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->getArchive()->getExcludedPackages() as $excludedPackage) {
				$statement->execute([
					$this->queue->packageID,
					$excludedPackage['name'],
					!empty($excludedPackage['version']) ? $excludedPackage['version'] : ''
				]);
			}
		}
		
		// save compatible versions
		if (!empty($this->getArchive()->getCompatibleVersions())) {
			$sql = "INSERT INTO     wcf".WCF_N."_package_compatibility
						(packageID, version)
				VALUES          (?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->getArchive()->getCompatibleVersions() as $version) {
				$statement->execute([
					$this->queue->packageID,
					$version
				]);
			}
		}
		
		// insert requirements and dependencies
		$requirements = $this->getArchive()->getAllExistingRequirements();
		if (!empty($requirements)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_requirement
						(packageID, requirement)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($requirements as $identifier => $possibleRequirements) {
				$requirement = array_shift($possibleRequirements);
				
				$statement->execute([
					$this->queue->packageID,
					$requirement['packageID']
				]);
			}
		}
		
		if ($this->getPackage()->isApplication && $this->getPackage()->package != 'com.woltlab.wcf' && $this->getAction() == 'install' && empty($this->getPackage()->packageDir)) {
			$document = $this->promptPackageDir($applicationDirectory);
			if ($document !== null && $document instanceof FormDocument) {
				$installationStep->setDocument($document);
			}

			$installationStep->setSplitNode();
		}
		
		return $installationStep;
	}
	
	/**
	 * Creates a new package based on the given data and returns it.
	 * 
	 * @param	array	$packageData
	 * @return	Package
	 * @since	5.2
	 */
	protected function createPackage(array $packageData) {
		return PackageEditor::create($packageData);
	}
	
	/**
	 * Saves the localized package info.
	 */
	protected function saveLocalizedPackageInfos() {
		$package = new Package($this->queue->packageID);
		
		// localize package information
		$sql = "INSERT INTO	wcf".WCF_N."_language_item
					(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// get language list
		$languageList = new LanguageList();
		$languageList->readObjects();
		
		// workaround for WCFSetup
		if (!PACKAGE_ID) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_language_category
				WHERE	languageCategory = ?";
			$statement2 = WCF::getDB()->prepareStatement($sql);
			$statement2->execute(['wcf.acp.package']);
			$languageCategory = $statement2->fetchObject(LanguageCategory::class);
		}
		else {
			$languageCategory = LanguageFactory::getInstance()->getCategory('wcf.acp.package');
		}
		
		// save package name
		$this->saveLocalizedPackageInfo($statement, $languageList, $languageCategory, $package, 'packageName');
		
		// save package description
		$this->saveLocalizedPackageInfo($statement, $languageList, $languageCategory, $package, 'packageDescription');
		
		// update description and name
		$packageEditor = new PackageEditor($package);
		$packageEditor->update([
			'packageDescription' => 'wcf.acp.package.packageDescription.package'.$this->queue->packageID,
			'packageName' => 'wcf.acp.package.packageName.package'.$this->queue->packageID
		]);
	}
	
	/**
	 * Saves a localized package info.
	 * 
	 * @param	PreparedStatement	$statement
	 * @param	LanguageList		$languageList
	 * @param	LanguageCategory	$languageCategory
	 * @param	Package			$package
	 * @param	string			$infoName
	 */
	protected function saveLocalizedPackageInfo(PreparedStatement $statement, $languageList, LanguageCategory $languageCategory, Package $package, $infoName) {
		$infoValues = $this->getArchive()->getPackageInfo($infoName);
		
		// get default value for languages without specified information
		$defaultValue = '';
		if (isset($infoValues['default'])) {
			$defaultValue = $infoValues['default'];
		}
		else if (isset($infoValues['en'])) {
			// fallback to English
			$defaultValue = $infoValues['en'];
		}
		else if (isset($infoValues[WCF::getLanguage()->getFixedLanguageCode()])) {
			// fallback to the language of the current user
			$defaultValue = $infoValues[WCF::getLanguage()->getFixedLanguageCode()];
		}
		else if ($infoName == 'packageName') {
			// fallback to the package identifier for the package name
			$defaultValue = $this->getArchive()->getPackageInfo('name');
		}
		
		foreach ($languageList as $language) {
			$value = $defaultValue;
			if (isset($infoValues[$language->languageCode])) {
				$value = $infoValues[$language->languageCode];
			}
			
			$statement->execute([
				$language->languageID,
				'wcf.acp.package.'.$infoName.'.package'.$package->packageID,
				$value,
				$languageCategory->languageCategoryID,
				1
			]);
		}
	}
	
	/**
	 * Executes a package installation plugin.
	 * 
	 * @param	mixed[]		$nodeData
	 * @return	PackageInstallationStep
	 * @throws	SystemException
	 */
	protected function executePIP(array $nodeData) {
		$step = new PackageInstallationStep();
		
		if ($nodeData['pip'] == PackageArchive::VOID_MARKER) {
			return $step;
		}
		
		// fetch all pips associated with current PACKAGE_ID and include pips
		// previously installed by current installation queue
		$sql = "SELECT	pluginName, className
			FROM	wcf".WCF_N."_package_installation_plugin
			WHERE	pluginName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$nodeData['pip']]);
		$row = $statement->fetchArray();
		
		// PIP is unknown
		if (!$row || (strcmp($nodeData['pip'], $row['pluginName']) !== 0)) {
			throw new SystemException("unable to find package installation plugin '".$nodeData['pip']."'");
		}
		
		// valdidate class definition
		$className = $row['className'];
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'");
		}
		
		// set default value
		if (empty($nodeData['value'])) {
			$defaultValue = call_user_func([$className, 'getDefaultFilename']);
			if ($defaultValue) {
				$nodeData['value'] = $defaultValue;
			}
		}
		
		$plugin = new $className($this, $nodeData);
		
		if (!($plugin instanceof IPackageInstallationPlugin)) {
			throw new ImplementationException($className, IPackageInstallationPlugin::class);
		}
		
		// execute PIP
		$document = null;
		try {
			$document = $plugin->{$this->action}();
		}
		catch (SplitNodeException $e) {
			$step->setSplitNode($e);
		}
		
		if ($document !== null && ($document instanceof FormDocument)) {
			$step->setDocument($document);
			$step->setSplitNode();
		}
		
		return $step;
	}
	
	/**
	 * Displays a list to select optional packages or installs selection.
	 * 
	 * @param	string		$currentNode
	 * @param	array		$nodeData
	 * @return	PackageInstallationStep
	 */
	protected function selectOptionalPackages($currentNode, array $nodeData) {
		$installationStep = new PackageInstallationStep();
		
		$document = $this->promptOptionalPackages($nodeData);
		if ($document !== null && $document instanceof FormDocument) {
			$installationStep->setDocument($document);
			$installationStep->setSplitNode();
		}
		// insert new nodes for each package
		else if (is_array($document)) {
			// get target child node
			$node = $currentNode;
			$queue = $this->queue;
			$shiftNodes = false;
			
			foreach ($nodeData as $package) {
				if (in_array($package['package'], $document)) {
					// ignore uninstallable packages
					if (!$package['isInstallable']) {
						continue;
					}
					
					if (!$shiftNodes) {
						$this->nodeBuilder->shiftNodes($currentNode, 'tempNode');
						$shiftNodes = true;
					}
					
					$queue = PackageInstallationQueueEditor::create([
						'parentQueueID' => $queue->queueID,
						'processNo' => $this->queue->processNo,
						'userID' => WCF::getUser()->userID,
						'package' => $package['package'],
						'packageName' => $package['packageName'],
						'archive' => $package['archive'],
						'action' => $queue->action
					]);
					
					$installation = new PackageInstallationDispatcher($queue);
					$installation->nodeBuilder->setParentNode($node);
					$installation->nodeBuilder->buildNodes();
					$node = $installation->nodeBuilder->getCurrentNode();
				}
				else {
					// remove archive
					@unlink($package['archive']);
				}
			}
			
			// shift nodes
			if ($shiftNodes) {
				$this->nodeBuilder->shiftNodes('tempNode', $node);
			}
		}
		
		return $installationStep;
	}
	
	/**
	 * Extracts files from .tar(.gz) archive and installs them
	 * 
	 * @param	string		$targetDir
	 * @param	string		$sourceArchive
	 * @param	IFileHandler	$fileHandler
	 * @return	Installer
	 */
	public function extractFiles($targetDir, $sourceArchive, $fileHandler = null) {
		return new Installer($targetDir, $sourceArchive, $fileHandler);
	}
	
	/**
	 * Returns current package.
	 * 
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage() {
		if ($this->package === null) {
			$this->package = new Package($this->queue->packageID);
		}
		
		return $this->package;
	}
	
	/**
	 * Prompts for a text input for package directory (applies for applications only)
	 * 
	 * @param       string          $applicationDirectory
	 * @return	FormDocument
	 */
	protected function promptPackageDir($applicationDirectory) {
		// check for pre-defined directories originating from WCFSetup
		$directory = WCF::getSession()->getVar('__wcfSetup_directories');
		$abbreviation = Package::getAbbreviation($this->getPackage()->package);
		if ($directory !== null) {
			$directory = $directory[$abbreviation] ?? null;
		}
		else if (ENABLE_ENTERPRISE_MODE && defined('ENTERPRISE_MODE_APP_DIRECTORIES') && is_array(ENTERPRISE_MODE_APP_DIRECTORIES)) {
			$directory = ENTERPRISE_MODE_APP_DIRECTORIES[$abbreviation] ?? null; 
		}
		
		if ($directory === null && !PackageInstallationFormManager::findForm($this->queue, 'packageDir')) {
			$container = new GroupFormElementContainer();
			$packageDir = new TextInputFormElement($container);
			$packageDir->setName('packageDir');
			$packageDir->setLabel(WCF::getLanguage()->get('wcf.acp.package.packageDir.input'));
			
			// check if there are packages installed in a parent
			// directory of WCF, or if packages are below it
			$sql = "SELECT  packageDir
				FROM    wcf".WCF_N."_package
				WHERE   packageDir <> ''";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			
			$isParent = null;
			while ($column = $statement->fetchColumn()) {
				if ($isParent !== null) {
					continue;
				}
				
				if (preg_match('~^\.\./[^\.]~', $column)) {
					$isParent = false;
				}
				else if (mb_strpos($column, '.') !== 0) {
					$isParent = true;
				}
			}
			
			$defaultPath = WCF_DIR;
			if ($isParent === false) {
				$defaultPath = dirname(WCF_DIR);
			}
			if (!$applicationDirectory) {
				$applicationDirectory = Package::getAbbreviation($this->getPackage()->package);
			}
			$defaultPath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($defaultPath)) . $applicationDirectory . '/';
			
			$packageDir->setValue($defaultPath);
			$container->appendChild($packageDir);
			
			$document = new FormDocument('packageDir');
			$document->appendContainer($container);
			
			PackageInstallationFormManager::registerForm($this->queue, $document);
			return $document;
		}
		else {
			if ($directory !== null) {
				$document = null;
				$packageDir = $directory;
			}
			else {
				$document = PackageInstallationFormManager::getForm($this->queue, 'packageDir');
				$document->handleRequest();
				$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(FileUtil::unifyDirSeparator($document->getValue('packageDir'))));
				if ($packageDir === '/') $packageDir = '';
			}
			
			if ($packageDir !== null) {
				// validate package dir
				if ($document !== null && file_exists($packageDir . 'global.php')) {
					$document->setError('packageDir', WCF::getLanguage()->get('wcf.acp.package.packageDir.notAvailable'));
					return $document;
				}
				
				// set package dir
				$packageEditor = new PackageEditor($this->getPackage());
				$packageEditor->update([
					'packageDir' => FileUtil::getRelativePath(WCF_DIR, $packageDir)
				]);
				
				// determine domain path, in some environments (e.g. ISPConfig) the $_SERVER paths are
				// faked and differ from the real filesystem path
				if (PACKAGE_ID) {
					$wcfDomainPath = ApplicationHandler::getInstance()->getWCF()->domainPath;
				}
				else {
					$sql = "SELECT	domainPath
						FROM	wcf".WCF_N."_application
						WHERE	packageID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([1]);
					$row = $statement->fetchArray();
					
					$wcfDomainPath = $row['domainPath'];
				}
				
				$documentRoot = substr(FileUtil::unifyDirSeparator(WCF_DIR), 0, -strlen(FileUtil::unifyDirSeparator($wcfDomainPath)));
				$domainPath = FileUtil::getRelativePath($documentRoot, $packageDir);
				if ($domainPath === './') {
					// `FileUtil::getRelativePath()` returns `./` if both paths lead to the same directory
					$domainPath = '/';
				}
				
				$domainPath = FileUtil::addLeadingSlash($domainPath);
				
				// update application path
				$application = new Application($this->getPackage()->packageID);
				$applicationEditor = new ApplicationEditor($application);
				$applicationEditor->update(['domainPath' => $domainPath]);
				
				// create directory and set permissions
				@mkdir($packageDir, 0777, true);
				FileUtil::makeWritable($packageDir);
			}
			
			return null;
		}
	}
	
	/**
	 * Prompts a selection of optional packages.
	 * 
	 * @param	string[][]	$packages
	 * @return	mixed
	 */
	protected function promptOptionalPackages(array $packages) {
		if (!PackageInstallationFormManager::findForm($this->queue, 'optionalPackages')) {
			$container = new MultipleSelectionFormElementContainer();
			$container->setName('optionalPackages');
			$container->setLabel(WCF::getLanguage()->get('wcf.acp.package.optionalPackages'));
			$container->setDescription(WCF::getLanguage()->get('wcf.acp.package.optionalPackages.description'));
			
			foreach ($packages as $package) {
				$optionalPackage = new MultipleSelectionFormElement($container);
				$optionalPackage->setName('optionalPackages');
				$optionalPackage->setLabel($package['packageName']);
				$optionalPackage->setValue($package['package']);
				$optionalPackage->setDescription($package['packageDescription']);
				if (!$package['isInstallable']) {
					$optionalPackage->setDisabledMessage(WCF::getLanguage()->get('wcf.acp.package.install.optionalPackage.missingRequirements'));
				}
				
				$container->appendChild($optionalPackage);
			}
			
			$document = new FormDocument('optionalPackages');
			$document->appendContainer($container);
			
			PackageInstallationFormManager::registerForm($this->queue, $document);
			return $document;
		}
		else {
			$document = PackageInstallationFormManager::getForm($this->queue, 'optionalPackages');
			$document->handleRequest();
			
			return $document->getValue('optionalPackages');
		}
	}
	
	/**
	 * Returns current package id.
	 * 
	 * @return	integer
	 */
	public function getPackageID() {
		return $this->queue->packageID;
	}
	
	/**
	 * Returns current package name.
	 * 
	 * @return	string		package name
	 * @since	3.0
	 */
	public function getPackageName() {
		return $this->queue->packageName;
	}
	
	/**
	 * Returns current package installation type.
	 * 
	 * @return	string
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * Opens the package installation queue and
	 * starts the installation, update or uninstallation of the first entry.
	 * 
	 * @param	integer		$parentQueueID
	 * @param	integer		$processNo
	 */
	public static function openQueue($parentQueueID = 0, $processNo = 0) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", [WCF::getUser()->userID]);
		$conditions->add("parentQueueID = ?", [$parentQueueID]);
		if ($processNo != 0) $conditions->add("processNo = ?", [$processNo]);
		$conditions->add("done = ?", [0]);
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_queue
			".$conditions."
			ORDER BY	queueID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$packageInstallation = $statement->fetchArray();
		
		if (!isset($packageInstallation['queueID'])) {
			$url = LinkHandler::getInstance()->getLink('PackageList');
			HeaderUtil::redirect($url);
			exit;
		}
		else {
			$url = LinkHandler::getInstance()->getLink('PackageInstallationConfirm', [], 'queueID='.$packageInstallation['queueID']);
			HeaderUtil::redirect($url);
			exit;
		}
	}
	
	/**
	 * Checks the package installation queue for outstanding entries.
	 * 
	 * @return	integer
	 */
	public static function checkPackageInstallationQueue() {
		$sql = "SELECT		queueID
			FROM		wcf".WCF_N."_package_installation_queue
			WHERE		userID = ?
					AND parentQueueID = 0
					AND done = 0
			ORDER BY	queueID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([WCF::getUser()->userID]);
		$row = $statement->fetchArray();
		
		if (!$row) {
			return 0;
		}
		
		return $row['queueID'];
	}
	
	/**
	 * Executes post-setup actions.
	 */
	public function completeSetup() {
		// remove archives
		$sql = "SELECT	archive
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE	processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->queue->processNo]);
		while ($row = $statement->fetchArray()) {
			@unlink($row['archive']);
		}
		
		// delete queues
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
			WHERE		processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->queue->processNo]);
		
		// clear language files once whole installation is completed
		LanguageEditor::deleteLanguageFiles();
		
		// reset all caches
		CacheHandler::getInstance()->flushAll();
	}
	
	/**
	 * Updates queue information.
	 */
	public function updatePackage() {
		if (empty($this->queue->packageName)) {
			$queueEditor = new PackageInstallationQueueEditor($this->queue);
			$queueEditor->update([
				'packageName' => $this->getArchive()->getLocalizedPackageInfo('packageName')
			]);
			
			// reload queue
			$this->queue = new PackageInstallationQueue($this->queue->queueID);
		}
	}
	
	/**
	 * Validates specific php requirements.
	 * 
	 * @param	array		$requirements
	 * @return	mixed[][]
	 */
	public static function validatePHPRequirements(array $requirements) {
		$errors = [];
		
		// validate php version
		if (isset($requirements['version'])) {
			$passed = false;
			if (version_compare(PHP_VERSION, $requirements['version'], '>=')) {
				$passed = true;
			}
			
			if (!$passed) {
				$errors['version'] = [
					'required' => $requirements['version'],
					'installed' => PHP_VERSION
				];
			}
		}
		
		// validate extensions
		if (isset($requirements['extensions'])) {
			foreach ($requirements['extensions'] as $extension) {
				$passed = extension_loaded($extension) ? true : false;
				
				if (!$passed) {
					$errors['extension'][] = [
						'extension' => $extension
					];
				}
			}
		}
		
		// validate settings
		if (isset($requirements['settings'])) {
			foreach ($requirements['settings'] as $setting => $value) {
				$iniValue = ini_get($setting);
				
				$passed = self::compareSetting($setting, $value, $iniValue);
				if (!$passed) {
					$errors['setting'][] = [
						'setting' => $setting,
						'required' => $value,
						'installed' => ($iniValue === false) ? '(unknown)' : $iniValue
					];
				}
			}
		}
		
		// validate functions
		if (isset($requirements['functions'])) {
			foreach ($requirements['functions'] as $function) {
				$function = mb_strtolower($function);
				
				$passed = self::functionExists($function);
				if (!$passed) {
					$errors['function'][] = [
						'function' => $function
					];
				}
			}
		}
		
		// validate classes
		if (isset($requirements['classes'])) {
			foreach ($requirements['classes'] as $class) {
				$passed = false;
				
				// see: http://de.php.net/manual/en/language.oop5.basic.php
				if (preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*.~', $class)) {
					$globalClass = '\\'.$class;
					
					if (class_exists($globalClass, false)) {
						$passed = true;
					}
				}
				
				if (!$passed) {
					$errors['class'][] = [
						'class' => $class
					];
				}
			}
		}
		
		return $errors;
	}
	
	/**
	 * Validates if an function exists and is not blacklisted by suhosin extension.
	 * 
	 * @param	string		$function
	 * @return	boolean
	 * @see		http://de.php.net/manual/en/function.function-exists.php#77980
	 */
	protected static function functionExists($function) {
		if (extension_loaded('suhosin')) {
			$blacklist = @ini_get('suhosin.executor.func.blacklist');
			if (!empty($blacklist)) {
				$blacklist = explode(',', $blacklist);
				foreach ($blacklist as $disabledFunction) {
					$disabledFunction = mb_strtolower(StringUtil::trim($disabledFunction));
					
					if ($function == $disabledFunction) {
						return false;
					}
				}
			}
		}
		
		return function_exists($function);
	}
	
	/**
	 * Compares settings, converting values into compareable ones.
	 * 
	 * @param	string		$setting
	 * @param	string		$value
	 * @param	mixed		$compareValue
	 * @return	boolean
	 */
	protected static function compareSetting($setting, $value, $compareValue) {
		if ($compareValue === false) return false;
		
		$value = mb_strtolower($value);
		$trueValues = ['1', 'on', 'true'];
		$falseValues = ['0', 'off', 'false'];
		
		// handle values considered as 'true'
		if (in_array($value, $trueValues)) {
			return $compareValue ? true : false;
		}
		// handle values considered as 'false'
		else if (in_array($value, $falseValues)) {
			return (!$compareValue) ? true : false;
		}
		else if (!is_numeric($value)) {
			$compareValue = self::convertShorthandByteValue($compareValue);
			$value = self::convertShorthandByteValue($value);
		}
		
		return ($compareValue >= $value) ? true : false;
	}
	
	/**
	 * Converts shorthand byte values into an integer representing bytes.
	 * 
	 * @param	string		$value
	 * @return	integer
	 * @see		http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
	 */
	protected static function convertShorthandByteValue($value) {
		// convert into bytes
		$lastCharacter = mb_substr($value, -1);
		switch ($lastCharacter) {
			// gigabytes
			case 'g':
				return (int)$value * 1073741824;
			break;
			
			// megabytes
			case 'm':
				return (int)$value * 1048576;
			break;
			
			// kilobytes
			case 'k':
				return (int)$value * 1024;
			break;
			
			default:
				return $value;
			break;
		}
	}
}

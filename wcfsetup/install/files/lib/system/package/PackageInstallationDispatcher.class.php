<?php

namespace wcf\system\package;

use ParagonIE\ConstantTime\Hex;
use wcf\data\application\Application;
use wcf\data\application\ApplicationEditor;
use wcf\data\devtools\project\DevtoolsProjectAction;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\LanguageList;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\command\ClearCache;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\devtools\DevtoolsSetup;
use wcf\system\Environment;
use wcf\system\event\EventHandler;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\form\container\GroupFormElementContainer;
use wcf\system\form\container\MultipleSelectionFormElementContainer;
use wcf\system\form\element\MultipleSelectionFormElement;
use wcf\system\form\element\TextInputFormElement;
use wcf\system\form\FormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\package\command\RebuildBootstrapper;
use wcf\system\package\event\PackageListChanged;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\RouteHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\setup\IFileHandler;
use wcf\system\setup\Installer;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\JSON;

/**
 * PackageInstallationDispatcher handles the whole installation process.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageInstallationDispatcher
{
    /**
     * current installation type
     * @var string
     */
    protected $action = '';

    /**
     * instance of PackageArchive
     * @var PackageArchive
     */
    protected $archive;

    /**
     * instance of PackageInstallationNodeBuilder
     * @var PackageInstallationNodeBuilder
     */
    public $nodeBuilder;

    /**
     * instance of Package
     * @var Package
     */
    protected $package;

    /**
     * instance of PackageInstallationQueue
     * @var PackageInstallationQueue
     */
    public $queue;

    /**
     * default name of the config file
     * @var string
     */
    const CONFIG_FILE = 'app.config.inc.php';

    /**
     * data of previous package in queue
     * @var string[]
     */
    protected $previousPackageData;

    /**
     * Creates a new instance of PackageInstallationDispatcher.
     *
     * @param PackageInstallationQueue $queue
     */
    public function __construct(PackageInstallationQueue $queue)
    {
        $this->queue = $queue;
        $this->nodeBuilder = new PackageInstallationNodeBuilder($this);

        $this->action = $this->queue->action;
    }

    /**
     * Sets data of previous package in queue.
     *
     * @param string[] $packageData
     */
    public function setPreviousPackage(array $packageData)
    {
        $this->previousPackageData = $packageData;
    }

    /**
     * Installs node components and returns next node.
     *
     * @throws      SystemException
     */
    public function install(string $node): PackageInstallationStep
    {
        $nodes = $this->nodeBuilder->getNodeData($node);
        if (empty($nodes)) {
            // guard against possible issues with empty instruction blocks, including
            // these blocks that contain no valid instructions at all (e.g. typo from
            // copy & paste)
            throw new SystemException(
                "Failed to retrieve nodes for identifier '{$node}', the query returned no results."
            );
        }

        // invoke node-specific actions
        $step = null;
        foreach ($nodes as $data) {
            $nodeData = \unserialize($data['nodeData']);
            $this->logInstallationStep($data);

            $step = match ($data['nodeType']) {
                'start' => $this->handleStartMarker($nodeData),
                'package' => $this->installPackage($nodeData),
                'pip' => $this->executePIP($nodeData),
                'optionalPackages' => $this->selectOptionalPackages($node, $nodeData),
                'end' => $this->handleEndMarker($nodeData),
            };

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
            (new AuditLogger())->log(
                <<<EOT
                Finalizing process
                ==================
                Process#: {$this->queue->processNo}
                EOT
            );

            $this->logInstallationStep([], 'start cleanup');

            // update "last update time" option
            $sql = "UPDATE  wcf1_option
                    SET     optionValue = ?
                    WHERE   optionName = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                TIME_NOW,
                'last_update_time',
            ]);

            if ($this->action == 'install') {
                // save localized package infos
                $this->saveLocalizedPackageInfos();

                if (!PACKAGE_ID) {
                    $this->finalizeWcfSetup();
                }

                // rebuild application paths
                ApplicationHandler::rebuild();
            }

            // rebuild config files for affected applications
            $sql = "SELECT      package.packageID
                    FROM        wcf1_package_installation_queue queue,
                                wcf1_package package
                    WHERE       queue.processNo = ?
                            AND package.packageID = queue.packageID
                            AND package.isApplication = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->queue->processNo,
                1,
            ]);
            while ($row = $statement->fetchArray()) {
                Package::writeConfigFile($row['packageID']);
            }

            SearchIndexManager::getInstance()->createSearchIndices();

            VersionTracker::getInstance()->createStorageTables();

            $command = new RebuildBootstrapper();
            $command();

            EventHandler::getInstance()->fire(new PackageListChanged());

            EventHandler::getInstance()->fireAction($this, 'postInstall');

            // remove archives
            $sql = "SELECT  archive
                    FROM    wcf1_package_installation_queue
                    WHERE   processNo = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->processNo]);
            while ($row = $statement->fetchArray()) {
                @\unlink($row['archive']);
            }

            // delete queues
            $sql = "DELETE FROM wcf1_package_installation_queue
                    WHERE       processNo = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->processNo]);

            $command = new ClearCache();
            $command();

            $this->logInstallationStep([], 'finished cleanup');

            (new AuditLogger())->log(
                <<<EOT
                Finalized process
                =================
                Process#: {$this->queue->processNo}
                EOT
            );
        }

        WCF::resetZendOpcache();

        return $step;
    }

    /**
     * @since 6.0
     */
    protected function finalizeWcfSetup(): void
    {
        HeaderUtil::setCookie(
            'user_session',
            CryptoUtil::createSignedString(
                \pack(
                    'CA20C',
                    1,
                    Hex::decode(WCF::getSession()->sessionID),
                    0
                )
            )
        );

        if (WCF::getSession()->getVar('__wcfSetup_developerMode')) {
            $this->setupDeveloperMode();
            WCF::getSession()->unregister('__wcfSetup_developerMode');
        }

        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            Environment::SYSTEM_ID_REGISTRY_KEY,
            Environment::getSystemId()
        );

        WCF::getSession()->register('__wcfSetup_completed', true);
    }

    /**
     * @since   5.5
     */
    protected function setupDeveloperMode(): void
    {
        $sql = "UPDATE  wcf1_option
                SET     optionValue = ?
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);

        $statement->execute([
            1,
            'enable_debug_mode',
        ]);
        $statement->execute([
            'public',
            'exception_privacy',
        ]);
        $statement->execute([
            'debugFolder',
            'mail_send_method',
        ]);
        $statement->execute([
            1,
            'enable_developer_tools',
        ]);
        $statement->execute([
            1,
            'log_missing_language_items',
        ]);
        $statement->execute([
            0,
            'offline',
        ]);
        $statement->execute([
            -1,
            'first_time_setup_state',
        ]);

        foreach (DevtoolsSetup::getInstance()->getOptionOverrides() as $optionName => $optionValue) {
            $statement->execute([
                $optionValue,
                $optionName,
            ]);
        }

        foreach (DevtoolsSetup::getInstance()->getUsers() as $newUser) {
            try {
                (new UserAction([], 'create', [
                    'data' => [
                        'email' => $newUser['email'],
                        'password' => $newUser['password'],
                        'username' => $newUser['username'],
                    ],
                    'groups' => [
                        1,
                        3,
                    ],
                ]))->executeAction();
            } catch (\LogicException $e) {
                // ignore errors due to event listeners missing at this
                // point during installation
            }
        }

        $importPath = DevtoolsSetup::getInstance()->getDevtoolsImportPath();
        if ($importPath !== '') {
            (new DevtoolsProjectAction([], 'quickSetup', [
                'path' => $importPath,
            ]))->executeAction();
        }

        $packageServerLogin = DevtoolsSetup::getInstance()->getPackageServerLogin();
        if (!empty($packageServerLogin)) {
            // All update servers installed at this point are only our own servers for which the same
            // login data can be used.
            $sql = "UPDATE  wcf1_package_update_server
                    SET     loginUsername = ?,
                            loginPassword = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $packageServerLogin['username'],
                $packageServerLogin['password'],
            ]);
        }
    }

    /**
     * Logs an installation step.
     *
     * @param array $node data of the executed node
     * @param $log optional additional log text
     */
    protected function logInstallationStep(array $node = [], string $log = ''): void
    {
        $time = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $logEntry = "[" . $time->format('Y-m-d\TH:i:s.uP') . "]\n";
        if ($node !== []) {
            $logEntry .= 'sequenceNo: ' . $node['sequenceNo'] . "\n";
            $logEntry .= 'nodeType: ' . $node['nodeType'] . "\n";
            $logEntry .= "nodeData:\n";

            $nodeData = \unserialize($node['nodeData']);
            foreach ($nodeData as $index => $value) {
                $logEntry .= "\t" . $index . ': ' . (!\is_object($value) && !\is_array($value) ? $value : JSON::encode($value)) . "\n";
            }
        }

        if ($log !== '') {
            $logEntry .= 'additional information: ' . $log . "\n";
        }

        $logEntry .= \str_repeat('-', 30) . "\n\n";

        \file_put_contents(
            WCF_DIR . 'log/' . \date('Y-m-d', TIME_NOW) . '-update-' . $this->queue->queueID . '.txt',
            $logEntry,
            \FILE_APPEND
        );
    }

    protected function handleStartMarker(array $nodeData)
    {
        (new AuditLogger())->log(
            <<<EOT
            Starting queue
            ==============
            Queue#: {$this->queue->queueID}
            EOT
        );

        if ($nodeData['currentPackageVersion'] !== null) {
            $expectedPackageVersion = $nodeData['currentPackageVersion'];

            $sql = "SELECT  packageVersion
                    FROM    wcf1_package
                    WHERE   packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->packageID]);

            $actualPackageVersion = $statement->fetchSingleColumn();

            if ($expectedPackageVersion !== $actualPackageVersion) {
                throw new \Exception(\sprintf(
                    "Expected '%s' to be installed in version '%s', but it is installed in version '%s'.",
                    $this->queue->package,
                    $expectedPackageVersion,
                    $actualPackageVersion
                ));
            }
        }

        return new PackageInstallationStep();
    }

    protected function handleEndMarker()
    {
        (new AuditLogger())->log(
            <<<EOT
            Ending queue
            ============
            Queue#: {$this->queue->queueID}
            EOT
        );

        return new PackageInstallationStep();
    }

    /**
     * Returns current package archive.
     *
     * @return  PackageArchive
     */
    public function getArchive()
    {
        if ($this->archive === null) {
            // check if we're doing an iterative update of the same package
            if (
                $this->previousPackageData !== null
                && $this->getPackage()->package == $this->previousPackageData['package']
            ) {
                if (
                    Package::compareVersion(
                        $this->getPackage()->packageVersion,
                        $this->previousPackageData['packageVersion'],
                        '<'
                    )
                ) {
                    // fake package to simulate the package version required by current archive
                    $this->getPackage()->setPackageVersion($this->previousPackageData['packageVersion']);
                }
            }

            $this->archive = new PackageArchive($this->queue->archive);
            if (!\str_starts_with(FileUtil::unifyDirSeparator(\realpath($this->archive->getArchive())), \TMP_DIR)) {
                throw new \Exception('Refusing to handle an archive outside of the temporary directory.');
            }
            $this->archive->openArchive();
        }

        return $this->archive;
    }

    /**
     * Installs current package.
     *
     * @param mixed[] $nodeData
     * @throws  SystemException
     */
    protected function installPackage(array $nodeData): PackageInstallationStep
    {
        $installationStep = new PackageInstallationStep();

        // check requirements
        foreach ($nodeData['requirements'] as $package => $requirementData) {
            // get existing package
            if ($requirementData['packageID']) {
                $sql = "SELECT  packageName, packageVersion
                        FROM    wcf1_package
                        WHERE   packageID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$requirementData['packageID']]);
            } else {
                // try to find matching package
                $sql = "SELECT  packageName, packageVersion
                        FROM    wcf1_package
                        WHERE   package = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$package]);
            }
            $row = $statement->fetchArray();

            // package is required but not available
            if ($row === false) {
                throw new SystemException("Package '" . $package . "' is required by '" . $nodeData['packageName'] . "', but is neither installed nor shipped.");
            }

            // check version requirements
            if ($requirementData['minVersion']) {
                if (Package::compareVersion($row['packageVersion'], $requirementData['minVersion']) < 0) {
                    throw new SystemException("Package '" . $nodeData['packageName'] . "' requires package '" . $row['packageName'] . "' in version '" . $requirementData['minVersion'] . "', but only version '" . $row['packageVersion'] . "' is installed");
                }
            }
        }
        unset($nodeData['requirements']);

        $applicationDirectory = $nodeData['applicationDirectory'];
        unset($nodeData['applicationDirectory']);

        // update package
        if ($this->queue->packageID) {
            $packageEditor = new PackageEditor(new Package($this->queue->packageID));
            unset($nodeData['installDate']);
            $packageEditor->update($nodeData);

            // delete old excluded packages
            $sql = "DELETE FROM wcf1_package_exclusion
                    WHERE       packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->packageID]);

            // delete old requirements and dependencies
            $sql = "DELETE FROM wcf1_package_requirement
                    WHERE       packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->packageID]);
        } else {
            // create package entry
            $package = $this->createPackage($nodeData);

            // update package id for current queue
            $queueEditor = new PackageInstallationQueueEditor($this->queue);
            $queueEditor->update(['packageID' => $package->packageID]);

            // reload queue
            $this->queue = new PackageInstallationQueue($this->queue->queueID);
            $this->package = null;

            if ($package->isApplication) {
                $host = \str_replace(RouteHandler::getProtocol(), '', RouteHandler::getHost());
                $path = RouteHandler::getPath(['acp']);

                $isTainted = 1;
                if ($this->getPackage()->package == 'com.woltlab.wcf') {
                    // com.woltlab.wcf is special, because promptPackageDir() will not be executed.
                    $isTainted = 0;
                }

                // insert as application
                ApplicationEditor::create([
                    'domainName' => $host,
                    'domainPath' => $path,
                    'cookieDomain' => $host,
                    'packageID' => $package->packageID,
                    'isTainted' => $isTainted,
                ]);
            }
        }

        // save excluded packages
        $sql = "INSERT INTO wcf1_package_exclusion
                            (packageID, excludedPackage, excludedPackageVersion)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($this->getArchive()->getExcludedPackages() as $excludedPackage) {
            $statement->execute([
                $this->queue->packageID,
                $excludedPackage['name'],
                $excludedPackage['version'],
            ]);
        }

        $requirements = $this->getArchive()->getExistingRequirements();
        \assert(
            \count($requirements) === \count($this->getArchive()->getRequirements()),
            "The existence of all requirements has been checked at the start of the method."
        );

        $sql = "INSERT INTO wcf1_package_requirement
                            (packageID, requirement)
                VALUES      (?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($requirements as $requirement) {
            $statement->execute([
                $this->queue->packageID,
                $requirement['packageID'],
            ]);
        }

        if (
            $this->getPackage()->isApplication
            && $this->getPackage()->package != 'com.woltlab.wcf'
            && $this->getAction() == 'install'
            && empty($this->getPackage()->packageDir)
        ) {
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
     * @param array $packageData
     * @return  Package
     * @since   5.2
     */
    protected function createPackage(array $packageData)
    {
        if (!PACKAGE_ID && $packageData['package'] === 'com.woltlab.wcf') {
            $packageEditor = new PackageEditor(new Package(1));
            $packageEditor->update($packageData);

            return new Package(1);
        }

        return PackageEditor::create($packageData);
    }

    /**
     * Saves the localized package info.
     */
    protected function saveLocalizedPackageInfos()
    {
        $package = new Package($this->queue->packageID);

        // localize package information
        $sql = "INSERT INTO wcf1_language_item
                            (languageID, languageItem, languageItemValue, languageCategoryID, packageID)
                VALUES      (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        // get language list
        $languageList = new LanguageList();
        $languageList->readObjects();

        // workaround for WCFSetup
        if (!PACKAGE_ID) {
            $sql = "SELECT  *
                    FROM    wcf1_language_category
                    WHERE   languageCategory = ?";
            $statement2 = WCF::getDB()->prepare($sql);
            $statement2->execute(['wcf.acp.package']);
            $languageCategory = $statement2->fetchObject(LanguageCategory::class);
        } else {
            $languageCategory = LanguageFactory::getInstance()->getCategory('wcf.acp.package');
        }

        // save package name
        $this->saveLocalizedPackageInfo($statement, $languageList, $languageCategory, $package, 'packageName');

        // save package description
        $this->saveLocalizedPackageInfo($statement, $languageList, $languageCategory, $package, 'packageDescription');

        // update description and name
        $packageEditor = new PackageEditor($package);
        $packageEditor->update([
            'packageDescription' => 'wcf.acp.package.packageDescription.package' . $this->queue->packageID,
            'packageName' => 'wcf.acp.package.packageName.package' . $this->queue->packageID,
        ]);
    }

    /**
     * Saves a localized package info.
     *
     * @param PreparedStatement $statement
     * @param LanguageList $languageList
     * @param LanguageCategory $languageCategory
     * @param Package $package
     * @param string $infoName
     */
    protected function saveLocalizedPackageInfo(
        PreparedStatement $statement,
        $languageList,
        LanguageCategory $languageCategory,
        Package $package,
        $infoName
    ) {
        $infoValues = $this->getArchive()->getPackageInfo($infoName);

        // get default value for languages without specified information
        $defaultValue = '';
        if (isset($infoValues['default'])) {
            $defaultValue = $infoValues['default'];
        } elseif (isset($infoValues['en'])) {
            // fallback to English
            $defaultValue = $infoValues['en'];
        } elseif (isset($infoValues[WCF::getLanguage()->getFixedLanguageCode()])) {
            // fallback to the language of the current user
            $defaultValue = $infoValues[WCF::getLanguage()->getFixedLanguageCode()];
        } elseif ($infoName == 'packageName') {
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
                'wcf.acp.package.' . $infoName . '.package' . $package->packageID,
                $value,
                $languageCategory->languageCategoryID,
                1,
            ]);
        }
    }

    /**
     * Executes a package installation plugin.
     *
     * @param mixed[] $nodeData
     * @throws  SystemException
     */
    protected function executePIP(array $nodeData): PackageInstallationStep
    {
        $step = new PackageInstallationStep();

        if ($nodeData['pip'] == PackageArchive::VOID_MARKER) {
            return $step;
        }

        // fetch all pips associated with current PACKAGE_ID and include pips
        // previously installed by current installation queue
        $sql = "SELECT  pluginName, className
                FROM    wcf1_package_installation_plugin
                WHERE   pluginName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$nodeData['pip']]);
        $row = $statement->fetchArray();

        // PIP is unknown
        if (!$row || $nodeData['pip'] !== $row['pluginName']) {
            throw new SystemException("unable to find package installation plugin '" . $nodeData['pip'] . "'");
        }

        // valdidate class definition
        $className = $row['className'];
        if (!\class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        // set default value
        if (empty($nodeData['value'])) {
            $defaultValue = \call_user_func([$className, 'getDefaultFilename']);
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
        } catch (SplitNodeException $e) {
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
     * @param string $currentNode
     * @param array $nodeData
     */
    protected function selectOptionalPackages(string $currentNode, array $nodeData): PackageInstallationStep
    {
        $installationStep = new PackageInstallationStep();

        $document = $this->promptOptionalPackages($nodeData);
        if ($document !== null && $document instanceof FormDocument) {
            $installationStep->setDocument($document);
            $installationStep->setSplitNode();
        } // insert new nodes for each package
        elseif (\is_array($document)) {
            // get target child node
            $node = $currentNode;
            $queue = $this->queue;
            $shiftNodes = false;

            foreach ($nodeData as $package) {
                if (\in_array($package['package'], $document)) {
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
                        'action' => $queue->action,
                    ]);

                    $installation = new self($queue);
                    $installation->nodeBuilder->setParentNode($node);
                    $installation->nodeBuilder->buildNodes();
                    $node = $installation->nodeBuilder->getCurrentNode();
                } else {
                    // remove archive
                    @\unlink($package['archive']);
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
     * @param string $targetDir
     * @param string $sourceArchive
     * @param IFileHandler $fileHandler
     * @return  Installer
     */
    public function extractFiles($targetDir, $sourceArchive, $fileHandler = null)
    {
        return new Installer($targetDir, $sourceArchive, $fileHandler);
    }

    /**
     * Returns current package.
     *
     * @return  \wcf\data\package\Package
     */
    public function getPackage()
    {
        if ($this->package === null) {
            $this->package = new Package($this->queue->packageID);
        }

        return $this->package;
    }

    /**
     * Prompts for a text input for package directory (applies for applications only)
     *
     * @param string $applicationDirectory
     * @return  FormDocument|null
     */
    protected function promptPackageDir($applicationDirectory)
    {
        $directory = null;
        $abbreviation = Package::getAbbreviation($this->getPackage()->package);

        if (!$applicationDirectory) {
            $applicationDirectory = $abbreviation;
        }

        if (
            WCF::getSession()->getVar('__wcfSetup_developerMode')
            && (
                isset($_ENV['WCFSETUP_USEDEFAULTWCFDIR'])
                || DevtoolsSetup::getInstance()->useDefaultInstallPath()
            )
        ) {
            $directory = WCF_DIR . $applicationDirectory . '/';
        } elseif (
            ENABLE_ENTERPRISE_MODE
            && \defined('ENTERPRISE_MODE_APP_DIRECTORIES')
            && \is_array(ENTERPRISE_MODE_APP_DIRECTORIES)
        ) {
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
                    FROM    wcf1_package
                    WHERE   packageDir <> ''";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();

            $isParent = null;
            while ($column = $statement->fetchColumn()) {
                if ($isParent !== null) {
                    continue;
                }

                if (\preg_match('~^\.\./[^\.]~', $column)) {
                    $isParent = false;
                } elseif (\mb_strpos($column, '.') !== 0) {
                    $isParent = true;
                }
            }

            $defaultPath = WCF_DIR;
            if ($isParent === false) {
                $defaultPath = \dirname(WCF_DIR);
            }
            $defaultPath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($defaultPath)) . $applicationDirectory . '/';

            $packageDir->setValue($defaultPath);
            $container->appendChild($packageDir);

            $document = new FormDocument('packageDir');
            $document->appendContainer($container);

            PackageInstallationFormManager::registerForm($this->queue, $document);

            return $document;
        } else {
            if ($directory !== null) {
                $document = null;
                $packageDir = $directory;
            } else {
                $document = PackageInstallationFormManager::getForm($this->queue, 'packageDir');
                $document->handleRequest();
                $packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(FileUtil::unifyDirSeparator(
                    $document->getValue('packageDir')
                )));
                if ($packageDir === '/') {
                    $packageDir = '';
                }
            }

            if ($packageDir !== null) {
                // validate package dir
                if ($document !== null && \file_exists($packageDir . 'global.php')) {
                    $document->setError(
                        'packageDir',
                        WCF::getLanguage()->get('wcf.acp.package.packageDir.notAvailable')
                    );

                    return $document;
                }

                // set package dir
                $packageEditor = new PackageEditor($this->getPackage());
                $packageEditor->update([
                    'packageDir' => FileUtil::getRelativePath(WCF_DIR, $packageDir),
                ]);

                // determine domain path, in some environments (e.g. ISPConfig) the $_SERVER paths are
                // faked and differ from the real filesystem path
                if (PACKAGE_ID) {
                    $wcfDomainPath = ApplicationHandler::getInstance()->getWCF()->domainPath;
                } else {
                    $sql = "SELECT  domainPath
                            FROM    wcf1_application
                            WHERE   packageID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([1]);
                    $row = $statement->fetchArray();

                    $wcfDomainPath = $row['domainPath'];
                }

                $documentRoot = \substr(
                    FileUtil::unifyDirSeparator(WCF_DIR),
                    0,
                    -\strlen(FileUtil::unifyDirSeparator($wcfDomainPath))
                );
                $domainPath = FileUtil::getRelativePath($documentRoot, $packageDir);
                if ($domainPath === './') {
                    // `FileUtil::getRelativePath()` returns `./` if both paths lead to the same directory
                    $domainPath = '/';
                }

                $domainPath = FileUtil::addLeadingSlash($domainPath);

                // update application path and untaint application
                $application = new Application($this->getPackage()->packageID);
                $applicationEditor = new ApplicationEditor($application);
                $applicationEditor->update([
                    'domainPath' => $domainPath,
                    'isTainted' => 0,
                ]);

                // create directory and set permissions
                @\mkdir($packageDir, 0777, true);
                FileUtil::makeWritable($packageDir);
            }

            return null;
        }
    }

    /**
     * Prompts a selection of optional packages.
     *
     * @param string[][] $packages
     * @return  mixed
     */
    protected function promptOptionalPackages(array $packages)
    {
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
                    $optionalPackage->setDisabledMessage(
                        WCF::getLanguage()->get('wcf.acp.package.install.optionalPackage.missingRequirements')
                    );
                }

                $container->appendChild($optionalPackage);
            }

            $document = new FormDocument('optionalPackages');
            $document->appendContainer($container);

            PackageInstallationFormManager::registerForm($this->queue, $document);

            return $document;
        } else {
            $document = PackageInstallationFormManager::getForm($this->queue, 'optionalPackages');
            $document->handleRequest();

            return $document->getValue('optionalPackages');
        }
    }

    /**
     * Returns current package id.
     *
     * @return  int
     */
    public function getPackageID()
    {
        return $this->queue->packageID;
    }

    /**
     * Returns current package name.
     *
     * @return  string      package name
     * @since   3.0
     */
    public function getPackageName()
    {
        return $this->queue->packageName;
    }

    /**
     * Returns current package installation type.
     *
     * @return  string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Updates queue information.
     */
    public function updatePackage()
    {
        if (empty($this->queue->packageName)) {
            $queueEditor = new PackageInstallationQueueEditor($this->queue);
            $queueEditor->update([
                'packageName' => $this->getArchive()->getLocalizedPackageInfo('packageName'),
            ]);

            // reload queue
            $this->queue = new PackageInstallationQueue($this->queue->queueID);
        }
    }
}

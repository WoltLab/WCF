<?php

namespace wcf\system\package;

use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\PackageEditor;
use wcf\event\package\PackageListChanged;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\PackageCacheBuilder;
use wcf\system\cache\command\ClearCache;
use wcf\system\event\EventHandler;
use wcf\system\package\command\RebuildBootstrapper;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\setup\Uninstaller;
use wcf\system\WCF;

/**
 * Handles the whole uninstallation process.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageUninstallationDispatcher extends PackageInstallationDispatcher
{
    /**
     * is true if the package's uninstall script has been executed or if no
     * such script exists
     * @var bool
     */
    protected $didExecuteUninstallScript = false;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Creates a new instance of PackageUninstallationDispatcher.
     *
     * @param PackageInstallationQueue $queue
     */
    public function __construct(PackageInstallationQueue $queue)
    {
        $this->queue = $queue;
        $this->nodeBuilder = new PackageUninstallationNodeBuilder($this);

        $this->action = $this->queue->action;
    }

    /**
     * Uninstalls node components and returns next node.
     */
    public function uninstall(string $node): PackageInstallationStep
    {
        $nodes = $this->nodeBuilder->getNodeData($node);

        // invoke node-specific actions
        foreach ($nodes as $data) {
            $nodeData = \unserialize($data['nodeData']);

            switch ($data['nodeType']) {
                case 'start':
                    $step = $this->handleStartMarker($nodeData);
                    break;

                case 'package':
                    $step = $this->uninstallPackage($nodeData);
                    break;

                case 'pip':
                    // the file pip is always executed last, thus, just before it,
                    // execute the uninstall script
                    if ($nodeData['pluginName'] == 'file' && !$this->didExecuteUninstallScript) {
                        $this->executeUninstallScript();

                        $this->didExecuteUninstallScript = true;
                    }

                    $step = $this->executePIP($nodeData);

                    if ($nodeData['pluginName'] == 'file') {
                        $command = new RebuildBootstrapper();
                        $command();
                    }
                    break;

                case 'end':
                    $step = $this->handleEndMarker($nodeData);
                    break;
            }
        }

        // mark node as completed
        $this->nodeBuilder->completeNode($node);

        // assign next node
        $node = $this->nodeBuilder->getNextNode($node);
        $step->setNode($node);

        // perform post-uninstall actions
        if ($node == '') {
            (new AuditLogger())->log(
                <<<EOT
                Finalizing process
                ==================
                Process#: {$this->queue->processNo}
                EOT
            );

            // rebuild application paths
            ApplicationHandler::rebuild();

            EventHandler::getInstance()->fire(new PackageListChanged());

            EventHandler::getInstance()->fireAction($this, 'postUninstall');

            // delete queues
            $sql = "DELETE FROM wcf1_package_installation_queue
                    WHERE       processNo = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->queue->processNo]);

            $command = new ClearCache();
            $command();

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
     * @inheritDoc
     */
    protected function executePIP(array $nodeData): PackageInstallationStep
    {
        /** @var IPackageInstallationPlugin $pip */
        $pip = new $nodeData['className']($this);

        $pip->uninstall();

        return new PackageInstallationStep();
    }

    /**
     * Executes the package's uninstall script (if existing).
     *
     * @since   3.0
     */
    protected function executeUninstallScript()
    {
        // check if uninstall script file for the uninstalled package exists
        $uninstallScript = WCF_DIR . 'acp/uninstall/' . $this->getPackage()->package . '.php';
        if (\file_exists($uninstallScript)) {
            include($uninstallScript);
        }
    }

    /**
     * Uninstalls current package.
     *
     * @param array $nodeData
     */
    protected function uninstallPackage(array $nodeData): PackageInstallationStep
    {
        PackageEditor::deleteAll([$this->queue->packageID]);

        // remove localized package info
        $sql = "DELETE FROM wcf" . WCF_N . "_language_item
                WHERE       languageItem IN (?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            'wcf.acp.package.packageName.package' . $this->queue->packageID,
            'wcf.acp.package.packageDescription.package' . $this->queue->packageID,
        ]);

        // reset package cache
        PackageCacheBuilder::getInstance()->reset();

        return new PackageInstallationStep();
    }

    /**
     * Deletes the given list of files from the target dir.
     *
     * @param string $targetDir
     * @param string[] $files
     * @param bool $deleteEmptyDirectories
     * @param bool $deleteEmptyTargetDir
     */
    public function deleteFiles($targetDir, $files, $deleteEmptyTargetDir = false, $deleteEmptyDirectories = true)
    {
        new Uninstaller($targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories);
    }
}

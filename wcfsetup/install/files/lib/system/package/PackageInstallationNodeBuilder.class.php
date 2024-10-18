<?php

namespace wcf\system\package;

use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\installation\queue\PackageInstallationQueueList;
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Creates a logical node-based installation tree.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageInstallationNodeBuilder
{
    /**
     * true if current node is empty
     * @var bool
     */
    public $emptyNode = true;

    /**
     * active package installation dispatcher
     * @var PackageInstallationDispatcher
     */
    public $installation;

    /**
     * current installation node
     * @var string
     */
    public $node = '';

    /**
     * current parent installation node
     * @var string
     */
    public $parentNode = '';

    /**
     * list of requirements to be checked before package installation
     * @var mixed[][]
     */
    public $requirements = [];

    /**
     * current sequence number within one node
     * @var int
     */
    public $sequenceNo = 0;

    /**
     * list of packages about to be installed
     * @var string[]
     */
    private static $pendingPackages = [];

    /**
     * Creates a new instance of PackageInstallationNodeBuilder
     *
     * @param PackageInstallationDispatcher $installation
     */
    public function __construct(PackageInstallationDispatcher $installation)
    {
        $this->installation = $installation;
    }

    /**
     * Sets parent node.
     */
    public function setParentNode(string $parentNode)
    {
        $this->parentNode = $parentNode;
    }

    /**
     * Builds nodes for current installation queue.
     */
    public function buildNodes()
    {
        $manifest = new PackageManifest($this->installation->getArchive());
        $auditLogger = new AuditLogger();

        $package = $this->installation->getPackage();
        switch ($this->installation->getAction()) {
            case 'install':
                $currentPackageVersion = null;

                $auditLogger->log(
                    <<<EOT
                    Building installation nodes
                    ===========================
                    Process#: {$this->installation->queue->processNo}
                    Queue#: {$this->installation->queue->queueID}
                    Parent Queue#: {$this->installation->queue->parentQueueID}
                    Parent Node: {$this->parentNode}

                    Archive: {$this->installation->getArchive()->getArchive()}
                    Manifest ({$manifest->getHash()}):
                    ---
                    {$manifest->getManifest()}
                    ---
                    EOT
                );
                break;
            case 'update':
                $currentPackageVersion = self::$pendingPackages[$package->package] ?? $package->packageVersion;

                $auditLogger->log(
                    <<<EOT
                    Building update nodes
                    =====================
                    Process#: {$this->installation->queue->processNo}
                    Queue#: {$this->installation->queue->queueID}
                    Parent Queue#: {$this->installation->queue->parentQueueID}
                    Parent Node: {$this->parentNode}

                    Package: {$package->package} ({$currentPackageVersion})

                    Archive: {$this->installation->getArchive()->getArchive()}
                    Manifest ({$manifest->getHash()}):
                    ---
                    {$manifest->getManifest()}
                    ---
                    EOT
                );
                break;
        }

        // required packages
        $this->buildRequirementNodes();

        $this->buildStartMarkerNode($currentPackageVersion);

        // install package itself
        if ($this->installation->getAction() == 'install') {
            $this->buildPackageNode();
        }

        // package installation plugins
        switch ($this->installation->getAction()) {
            case 'install':
                $instructions = $this->installation->getArchive()->getInstallInstructions();

                break;
            case 'update':
                $instructions = $this->installation->getArchive()->getUpdateInstructionsFor($currentPackageVersion) ?? [];

                break;
            default:
                throw new \LogicException('Unreachable');
        }

        $this->buildPluginNodes($instructions);

        // register package version
        self::$pendingPackages[$this->installation->getArchive()->getPackageInfo('name')] = $this->installation->getArchive()->getPackageInfo('version');

        // optional packages (ignored on update)
        if ($this->installation->getAction() == 'install') {
            $this->buildOptionalNodes();
        }

        if ($this->installation->getAction() == 'update') {
            $this->buildPackageNode();
        }

        $this->buildEndMarkerNode();

        $auditLogger->log(
            <<<EOT
            Finished building nodes
            =======================
            Process#: {$this->installation->queue->processNo}
            Queue#: {$this->installation->queue->queueID}
            Final Node: {$this->node}
            EOT
        );

        // child queues
        $this->buildChildQueues();
    }

    /**
     * Returns the succeeding node.
     */
    public function getNextNode(string $parentNode = ''): string
    {
        $sql = "SELECT  node
                FROM    wcf1_package_installation_node
                WHERE   processNo = ?
                    AND parentNode = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->processNo,
            $parentNode,
        ]);
        $row = $statement->fetchArray();

        if (!$row) {
            return '';
        }

        return $row['node'];
    }

    /**
     * Returns package name associated with given queue id.
     */
    public function getPackageNameByQueue(int $queueID): string
    {
        $sql = "SELECT  packageName
                FROM    wcf1_package_installation_queue
                WHERE   queueID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$queueID]);
        $row = $statement->fetchArray();

        if (!$row) {
            return '';
        }

        return $row['packageName'];
    }

    /**
     * Returns installation type by queue id.
     */
    public function getInstallationTypeByQueue(int $queueID): string
    {
        $sql = "SELECT  action
                FROM    wcf1_package_installation_queue
                WHERE   queueID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$queueID]);
        $row = $statement->fetchArray();

        return $row['action'];
    }

    /**
     * Returns data for current node.
     *
     * @return  array
     */
    public function getNodeData(string $node)
    {
        $sql = "SELECT      nodeType, nodeData, sequenceNo
                FROM        wcf1_package_installation_node
                WHERE       processNo = ?
                        AND node = ?
                ORDER BY    sequenceNo ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->processNo,
            $node,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Marks a node as completed.
     */
    public function completeNode(string $node)
    {
        $sql = "UPDATE  wcf1_package_installation_node
                SET     done = 1
                WHERE   processNo = ?
                    AND node = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->processNo,
            $node,
        ]);
    }

    /**
     * Removes all nodes associated with queue's process no.
     *
     * CAUTION: This method SHOULD NOT be called within the installation process!
     */
    public function purgeNodes()
    {
        $sql = "DELETE FROM wcf1_package_installation_node
                WHERE       processNo = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->processNo,
        ]);

        $sql = "DELETE FROM wcf1_package_installation_form
                WHERE       queueID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->queueID,
        ]);
    }

    /**
     * Calculates current setup process.
     *
     * @param string $node
     * @return  int
     */
    public function calculateProgress($node)
    {
        $progress = [
            'done' => 0,
            'outstanding' => 0,
        ];

        $sql = "SELECT  done
                FROM    wcf1_package_installation_node
                WHERE   processNo = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->processNo,
        ]);
        while ($row = $statement->fetchArray()) {
            if ($row['done']) {
                $progress['done']++;
            } else {
                $progress['outstanding']++;
            }
        }

        if (!$progress['done']) {
            return 0;
        } elseif (!$progress['outstanding']) {
            return 100;
        } else {
            $total = $progress['done'] + $progress['outstanding'];

            return \round(($progress['done'] / $total) * 100);
        }
    }

    /**
     * Duplicates a node by re-inserting it and moving all descendants into a new tree.
     *
     * @param string $node
     * @param int $sequenceNo
     */
    public function cloneNode($node, $sequenceNo)
    {
        $newNode = $this->getToken();

        // update descendants
        $this->shiftNodes($node, $newNode);

        // create a copy of current node (prevents empty nodes)
        $sql = "SELECT  nodeType, nodeData, done
                FROM    wcf1_package_installation_node
                WHERE   node = ?
                    AND processNo = ?
                    AND sequenceNo = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $node,
            $this->installation->queue->processNo,
            $sequenceNo,
        ]);
        $row = $statement->fetchArray();

        $sql = "INSERT INTO wcf1_package_installation_node
                            (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData, done)
                VALUES      (?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->queueID,
            $this->installation->queue->processNo,
            $sequenceNo,
            $newNode,
            $node,
            $row['nodeType'],
            $row['nodeData'],
            $row['done'],
        ]);

        // move other child-nodes greater than $sequenceNo into new node
        $sql = "UPDATE  wcf1_package_installation_node
                SET     parentNode = ?,
                        node = ?
                WHERE   node = ?
                    AND processNo = ?
                    AND sequenceNo > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $node,
            $newNode,
            $node,
            $this->installation->queue->processNo,
            $sequenceNo,
        ]);
    }

    /**
     * Shifts nodes to allow dynamic inserts at runtime.
     */
    public function shiftNodes(string $oldParentNode, string $newParentNode)
    {
        $sql = "UPDATE  wcf1_package_installation_node
                SET     parentNode = ?
                WHERE   parentNode = ?
                    AND processNo = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $newParentNode,
            $oldParentNode,
            $this->installation->queue->processNo,
        ]);
    }

    protected function buildStartMarkerNode(?string $currentPackageVersion)
    {
        if (!empty($this->node)) {
            $this->parentNode = $this->node;
            $this->sequenceNo = 0;
        }
        $this->node = $this->getToken();

        $sql = "INSERT INTO wcf1_package_installation_node
                            (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
                VALUES      (?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->queueID,
            $this->installation->queue->processNo,
            $this->sequenceNo,
            $this->node,
            $this->parentNode,
            'start',
            \serialize([
                'currentPackageVersion' => $currentPackageVersion,
            ]),
        ]);
    }

    protected function buildEndMarkerNode()
    {
        if (!empty($this->node)) {
            $this->parentNode = $this->node;
            $this->sequenceNo = 0;
        }
        $this->node = $this->getToken();

        $sql = "INSERT INTO wcf1_package_installation_node
                            (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
                VALUES      (?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->queueID,
            $this->installation->queue->processNo,
            $this->sequenceNo,
            $this->node,
            $this->parentNode,
            'end',
            \serialize([]),
        ]);
    }

    /**
     * Builds package node used to install the package itself.
     */
    protected function buildPackageNode()
    {
        if (!empty($this->node)) {
            $this->parentNode = $this->node;
            $this->sequenceNo = 0;
        }

        $this->node = $this->getToken();

        $sql = "INSERT INTO wcf1_package_installation_node
                            (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
                VALUES      (?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->installation->queue->queueID,
            $this->installation->queue->processNo,
            $this->sequenceNo,
            $this->node,
            $this->parentNode,
            'package',
            \serialize([
                'package' => $this->installation->getArchive()->getPackageInfo('name'),
                'packageName' => $this->installation->getArchive()->getLocalizedPackageInfo('packageName'),
                'packageDescription' => $this->installation->getArchive()->getLocalizedPackageInfo('packageDescription'),
                'packageVersion' => $this->installation->getArchive()->getPackageInfo('version'),
                'packageDate' => $this->installation->getArchive()->getPackageInfo('date'),
                'packageURL' => $this->installation->getArchive()->getPackageInfo('packageURL'),
                'isApplication' => $this->installation->getArchive()->getPackageInfo('isApplication'),
                'author' => $this->installation->getArchive()->getAuthorInfo('author'),
                'authorURL' => $this->installation->getArchive()->getAuthorInfo('authorURL') ?: '',
                'installDate' => TIME_NOW,
                'updateDate' => TIME_NOW,
                'requirements' => $this->requirements,
                'applicationDirectory' => $this->installation->getArchive()->getPackageInfo('applicationDirectory') ?: '',
            ]),
        ]);
    }

    /**
     * Builds nodes for required packages, whereas each has it own node.
     *
     * @return  string
     * @throws  SystemException
     */
    protected function buildRequirementNodes()
    {
        $queue = $this->installation->queue;

        // handle requirements
        $requiredPackages = $this->installation->getArchive()->getOpenRequirements();
        foreach ($requiredPackages as $packageName => $package) {
            if (!isset($package['file'])) {
                if (
                    isset(self::$pendingPackages[$packageName])
                    && (
                        !isset($package['minversion'])
                        || Package::compareVersion(self::$pendingPackages[$packageName], $package['minversion']) >= 0
                    )
                ) {
                    // the package will already be installed and no
                    // minversion is given or the package which will be
                    // installed satisfies the minversion, thus we can
                    // ignore this requirement
                    continue;
                }

                // requirements will be checked once package is about to be installed
                $this->requirements[$packageName] = [
                    'minVersion' => $package['minversion'] ?? '',
                    'packageID' => $package['packageID'],
                ];

                continue;
            }

            if ($this->node == '' && !empty($this->parentNode)) {
                $this->node = $this->parentNode;
            }

            // extract package
            $index = $this->installation->getArchive()->getTar()->getIndexByFilename($package['file']);
            if ($index === false) {
                // workaround for WCFSetup
                if (!PACKAGE_ID && $packageName == 'com.woltlab.wcf') {
                    continue;
                }

                throw new SystemException("Unable to find required package '" . $package['file'] . "' within archive of package '" . $this->installation->queue->package . "'.");
            }

            $fileName = FileUtil::getTemporaryFilename(
                'package_',
                \preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', \basename($package['file']))
            );
            $this->installation->getArchive()->getTar()->extract($index, $fileName);

            // get archive data
            $archive = new PackageArchive($fileName);
            $archive->openArchive();

            // check if delivered package has correct identifier
            if ($archive->getPackageInfo('name') != $packageName) {
                throw new SystemException("Invalid package file delivered for '" . $packageName . "' requirement of package '" . $this->installation->getArchive()->getPackageInfo('name') . "' (delivered package: '" . $archive->getPackageInfo('name') . "').");
            }

            // check if delivered version satisfies minversion
            if (
                isset($package['minversion']) && Package::compareVersion(
                    $package['minversion'],
                    $archive->getPackageInfo('version')
                ) > 0
            ) {
                throw new SystemException("Package '" . $this->installation->getArchive()->getPackageInfo('name') . "' requires package '" . $packageName . "' at least in version " . $package['minversion'] . ", but only delivers version " . $archive->getPackageInfo('version') . ".");
            }

            // get package id
            $sql = "SELECT  packageID
                    FROM    wcf1_package
                    WHERE   package = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$archive->getPackageInfo('name')]);
            $row = $statement->fetchArray();
            $packageID = ($row === false) ? null : $row['packageID'];

            // check if package will already be installed
            if (isset(self::$pendingPackages[$packageName])) {
                if (
                    Package::compareVersion(
                        self::$pendingPackages[$packageName],
                        $archive->getPackageInfo('version')
                    ) >= 0
                ) {
                    // the version to be installed satisfies the required version
                    continue;
                } else {
                    // the new delivered required version of the package has a
                    // higher version number, thus update/replace the existing
                    // package installation queue
                }
            }

            if ($archive->getPackageInfo('name') === 'com.woltlab.wcf') {
                WCF::checkWritability();
            }

            // create new queue
            $queue = PackageInstallationQueueEditor::create([
                'parentQueueID' => $queue->queueID,
                'processNo' => $queue->processNo,
                'userID' => WCF::getUser()->userID,
                'package' => $archive->getPackageInfo('name'),
                'packageID' => $packageID,
                'packageName' => $archive->getLocalizedPackageInfo('packageName'),
                'archive' => $fileName,
                'action' => $packageID ? 'update' : 'install',
            ]);

            // spawn nodes
            $installation = new PackageInstallationDispatcher($queue);
            $installation->nodeBuilder->setParentNode($this->node);
            $installation->nodeBuilder->buildNodes();
            $this->node = $installation->nodeBuilder->getCurrentNode();
        }
    }

    /**
     * Returns current node
     */
    public function getCurrentNode(): string
    {
        return $this->node;
    }

    /**
     * Builds package installation plugin nodes, whereas pips could be grouped within
     * one node, differ from each by nothing but the sequence number.
     *
     * @return  string
     */
    protected function buildPluginNodes(array $instructions)
    {
        $count = \count($instructions);

        if ($count === 0) {
            // Abort if an empty list of instructions is received. This most likely indicates that
            // the update instructions have been erroneously discarded.
            throw new \Exception('Received an empty list of instructions.');
        }

        if (!empty($this->node)) {
            $this->parentNode = $this->node;
            $this->sequenceNo = 0;
        }

        $this->node = $this->getToken();

        $this->emptyNode = true;

        $i = 0;
        $pluginNodes = [];
        foreach ($instructions as $pip) {
            $i++;

            if (isset($pip['attributes']['run']) && ($pip['attributes']['run'] == 'standalone')) {
                // move into a new node unless current one is empty
                if (!$this->emptyNode) {
                    $this->parentNode = $this->node;
                    $this->node = $this->getToken();
                    $this->sequenceNo = 0;
                }
                $pluginNodes[] = [
                    'data' => $pip,
                    'node' => $this->node,
                    'parentNode' => $this->parentNode,
                    'sequenceNo' => $this->sequenceNo,
                ];

                // create a new node for following PIPs, unless it is the last one
                if ($i < $count) {
                    $this->parentNode = $this->node;
                    $this->node = $this->getToken();
                    $this->sequenceNo = 0;

                    $this->emptyNode = true;
                }
            } else {
                $this->sequenceNo++;

                $pluginNodes[] = [
                    'data' => $pip,
                    'node' => $this->node,
                    'parentNode' => $this->parentNode,
                    'sequenceNo' => $this->sequenceNo,
                ];

                $this->emptyNode = false;
            }
        }

        \assert($pluginNodes !== []);

        $sql = "INSERT INTO wcf1_package_installation_node
                            (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
                VALUES      (?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        foreach ($pluginNodes as $nodeData) {
            $statement->execute([
                $this->installation->queue->queueID,
                $this->installation->queue->processNo,
                $nodeData['sequenceNo'],
                $nodeData['node'],
                $nodeData['parentNode'],
                'pip',
                \serialize($nodeData['data']),
            ]);
        }
    }

    /**
     * Builds nodes for optional packages, whereas each package exists within
     * one node with the same parent node, separated by sequence no (which does
     * not really matter at this point).
     */
    protected function buildOptionalNodes()
    {
        $packages = [];

        $optionalPackages = $this->installation->getArchive()->getOptionals();
        foreach ($optionalPackages as $package) {
            // check if already installed
            if (Package::isAlreadyInstalled($package['name'])) {
                continue;
            }

            // extract package
            $index = $this->installation->getArchive()->getTar()->getIndexByFilename($package['file']);
            if ($index === false) {
                throw new SystemException("Unable to find required package '" . $package['file'] . "' within archive.");
            }

            $fileName = FileUtil::getTemporaryFilename(
                'package_',
                \preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', \basename($package['file']))
            );
            $this->installation->getArchive()->getTar()->extract($index, $fileName);

            // get archive data
            $archive = new PackageArchive($fileName);
            $archive->openArchive();

            // check if all requirements are met
            $isInstallable = true;
            foreach ($archive->getOpenRequirements() as $packageName => $requiredPackage) {
                if (!isset($requiredPackage['file'])) {
                    // requirement is neither installed nor shipped, check if it is about to be installed
                    if (!isset(self::$pendingPackages[$packageName])) {
                        $isInstallable = false;
                        break;
                    }
                }
            }

            // check for exclusions
            $excludedPackages = $archive->getConflictedExcludedPackages();
            if (!empty($excludedPackages)) {
                $isInstallable = false;
            }

            $excludingPackages = $archive->getConflictedExcludingPackages();
            if (!empty($excludingPackages)) {
                $isInstallable = false;
            }

            $packages[] = [
                'archive' => $fileName,
                'isInstallable' => $isInstallable,
                'package' => $archive->getPackageInfo('name'),
                'packageName' => $archive->getLocalizedPackageInfo('packageName'),
                'packageDescription' => $archive->getLocalizedPackageInfo('packageDescription'),
                'selected' => 0,
            ];

            self::$pendingPackages[$archive->getPackageInfo('name')] = $archive->getPackageInfo('version');
        }

        if (!empty($packages)) {
            $this->parentNode = $this->node;
            $this->node = $this->getToken();
            $this->sequenceNo = 0;

            $sql = "INSERT INTO wcf1_package_installation_node
                                (queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
                    VALUES      (?, ?, ?, ?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->installation->queue->queueID,
                $this->installation->queue->processNo,
                $this->sequenceNo,
                $this->node,
                $this->parentNode,
                'optionalPackages',
                \serialize($packages),
            ]);
        }
    }

    /**
     * Recursively build nodes for child queues.
     */
    protected function buildChildQueues()
    {
        $queueList = new PackageInstallationQueueList();
        $queueList->getConditionBuilder()->add(
            "package_installation_queue.parentQueueID = ?",
            [$this->installation->queue->queueID]
        );
        $queueList->getConditionBuilder()->add("package_installation_queue.queueID NOT IN (
            SELECT  queueID
            FROM    wcf1_package_installation_node
        )");
        $queueList->readObjects();

        foreach ($queueList as $queue) {
            $installation = new PackageInstallationDispatcher($queue);

            // work-around for iterative package updates
            if (isset(self::$pendingPackages[$queue->package])) {
                $installation->setPreviousPackage([
                    'package' => $queue->package,
                    'packageVersion' => self::$pendingPackages[$queue->package],
                ]);
            }

            $installation->nodeBuilder->setParentNode($this->node);
            $installation->nodeBuilder->buildNodes();
            $this->node = $installation->nodeBuilder->getCurrentNode();
        }
    }

    /**
     * Returns a short SHA1-hash.
     */
    protected function getToken(): string
    {
        return \mb_substr(StringUtil::getRandomID(), 0, 8);
    }

    /**
     * Returns queue id based upon current node.
     *
     * @return  int|null
     */
    public function getQueueByNode(int $processNo, string $node)
    {
        $sql = "SELECT  queueID
                FROM    wcf1_package_installation_node
                WHERE   processNo = ?
                    AND node = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $processNo,
            $node,
        ]);
        $row = $statement->fetchArray();

        if ($row === false) {
            // PHP <7.4 _silently_ returns `null` when attempting to read an array index
            // when the source value equals `false`.
            return null;
        }

        return $row['queueID'];
    }
}

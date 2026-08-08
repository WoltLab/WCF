<?php

namespace wcf\data\devtools\project;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Executes devtools project related actions.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<DevtoolsProject, DevtoolsProjectEditor>
 */
class DevtoolsProjectAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = DevtoolsProjectEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'deletePipEntry', 'installPackage'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.configuration.package.canInstallPackage'];

    /**
     * package installation queue for project to be installed from source
     * @var     ?PackageInstallationQueue
     * @since   5.2
     */
    public $queue;

    /**
     * package installation plugin the deleted entry belongs to
     * @var ?DevtoolsPip
     * @since   5.2
     */
    protected $pip;

    /**
     * @since   5.2
     */
    #[\Override]
    public function create()
    {
        $this->parameters['data']['path'] = FileUtil::addTrailingSlash($this->parameters['data']['path']);

        $project = parent::create();

        // ensure that the project directory exists
        FileUtil::makePath($project->path);

        return $project;
    }

    #[\Override]
    public function validateDelete()
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0) {
            throw new IllegalLinkException();
        }

        parent::validateDelete();
    }

    /**
     * Validates the 'quickSetup' action.
     *
     * @return void
     * @throws  IllegalLinkException
     */
    public function validateQuickSetup()
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $this->readString('path');
    }

    /**
     * Quickly setups multiple projects by scanning a directory.
     *
     * @return array{
     *  successMessage: string,
     * }|array{
     *  errorMessage: string,
     *  errorType: string,
     * }
     */
    public function quickSetup()
    {
        if (!\is_dir($this->parameters['path'])) {
            return [
                'errorMessage' => WCF::getLanguage()->get('wcf.acp.devtools.project.path.error.notFound'),
                'errorType' => 'notFound',
            ];
        }

        $path = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($this->parameters['path']));

        // read all project names and paths
        $projectList = new DevtoolsProjectList();
        $projectList->readObjects();

        $projectNames = $projectPaths = [];
        foreach ($projectList as $project) {
            $projectNames[] = $project->name;
            $projectPaths[] = $project->path;
        }

        $projectCount = 0;

        $directoryUtil = DirectoryUtil::getInstance($path, false);
        $directoryUtil->executeCallback(static function ($directory) use (
            $path,
            $projectNames,
            $projectPaths,
            &$projectCount
        ) {
            $projectPath = $path . $directory . '/';

            // validate path
            if (DevtoolsProject::validatePath($projectPath) !== '') {
                return;
            }

            // only consider paths that are not already used by a different project
            if (\in_array($projectPath, $projectPaths)) {
                return;
            }

            // make sure that project name is unique
            $name = $directory;

            $iteration = 1;
            while (\in_array($name, $projectNames)) {
                $name = $directory . ' (' . ($iteration++) . ')';
            }

            (new self([], 'create', [
                'data' => [
                    'name' => $name,
                    'path' => $projectPath,
                ],
            ]))->executeAction();

            $projectCount++;
        });

        if (!$projectCount) {
            return [
                'errorMessage' => WCF::getLanguage()->get('wcf.acp.devtools.project.quickSetup.path.error.noPackages'),
                'errorType' => 'noPackages',
            ];
        }

        return [
            'successMessage' => WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.quickSetup.success', [
                'count' => $projectCount,
            ]),
        ];
    }

    /**
     * Checks if the `installPackage` action can be executed.
     *
     * @return void
     * @throws  IllegalLinkException
     * @since   5.2
     */
    public function validateInstallPackage()
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $this->getSingleObject();
    }

    /**
     * Installs a package that is currently only available as a project.
     *
     * @return array{isApplication: int, queueID: int}
     * @since 5.2
     */
    public function installPackage()
    {
        $packageArchive = $this->getSingleObject()->getPackageArchive();
        $packageArchive->openArchive();

        $this->queue = PackageInstallationQueueEditor::create([
            'processNo' => PackageInstallationQueue::getNewProcessNo(),
            'userID' => WCF::getUser()->userID,
            'package' => $packageArchive->getPackageInfo('name'),
            'packageName' => $packageArchive->getLocalizedPackageInfo('packageName'),
            'packageID' => null,
            'archive' => '',
            'action' => 'install',
            'isApplication' => $packageArchive->getPackageInfo('isApplication') ? 1 : 0,
        ]);

        return [
            'isApplication' => $this->queue->isApplication,
            'queueID' => $this->queue->queueID,
        ];
    }

    /**
     * Checks if the `deletePipEntry` action can be executed.
     *
     * @return void
     * @throws  IllegalLinkException
     * @since   5.2
     */
    public function validateDeletePipEntry()
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $project = $this->getSingleObject();

        // read and validate pip
        $this->readString('pip');
        $filteredPips = \array_filter($project->getPips(), function (DevtoolsPip $pip) {
            return $pip->pluginName === $this->parameters['pip'];
        });
        if (\count($filteredPips) === 1) {
            $this->pip = \reset($filteredPips);
        } else {
            throw new IllegalLinkException();
        }

        if (!$this->pip->supportsGui()) {
            throw new IllegalLinkException();
        }

        // read and validate entry type
        $this->readString('entryType', true);
        $pip = $this->pip->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        if ($this->parameters['entryType'] !== '') {
            try {
                $pip->setEntryType($this->parameters['entryType']);
            } catch (\InvalidArgumentException $e) {
                throw new IllegalLinkException();
            }
        } elseif (!empty($pip->getEntryTypes())) {
            throw new IllegalLinkException();
        }

        // read and validate identifier
        $this->readString('identifier');
        $entryList = $pip->getEntryList();
        if (!$entryList->hasEntry($this->parameters['identifier'])) {
            throw new IllegalLinkException();
        }

        $this->readBoolean('addDeleteInstruction', true);
    }

    /**
     * Deletes a specific pip entry.
     *
     * @return  string[]    identifier of the deleted pip entry
     * @since   5.2
     */
    public function deletePipEntry()
    {
        $pip = $this->pip->getPip();
        \assert($pip instanceof IGuiPackageInstallationPlugin);
        $pip->deleteEntry($this->parameters['identifier'], $this->parameters['addDeleteInstruction']);

        return [
            'identifier' => $this->parameters['identifier'],
        ];
    }
}

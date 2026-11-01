<?php

namespace wcf\data\package\installation\plugin;

use wcf\command\devtools\project\SyncPackageInstallationPlugin;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes package installation plugin-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PackageInstallationPlugin, PackageInstallationPluginEditor>
 */
class PackageInstallationPluginAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageInstallationPluginEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['invoke'];

    /**
     * @var ?DevtoolsPip
     */
    public $devtoolsPip;

    /**
     * @var ?PackageInstallationPlugin
     */
    public $packageInstallationPlugin;

    /**
     * @var ?DevtoolsProject
     */
    public $project;

    /**
     * Validates parameters to invoke a single PIP.
     *
     * @return void
     * @throws PermissionDeniedException
     * @throws UserInputException
     */
    public function validateInvoke()
    {
        if (\ENABLE_DEVELOPER_TOOLS === 0 || !WCF::getSession()->hasPermission('admin.configuration.package.canInstallPackage')) {
            throw new PermissionDeniedException();
        }

        $this->readString('pluginName');
        $this->readInteger('projectID');
        $this->readString('target');

        $this->project = new DevtoolsProject($this->parameters['projectID']);
        if ($this->project->isNil() || $this->project->validate() !== '') {
            throw new UserInputException('projectID');
        }

        $this->packageInstallationPlugin = new PackageInstallationPlugin($this->parameters['pluginName']);
        if ($this->packageInstallationPlugin->isNil()) {
            throw new UserInputException('pluginName');
        }

        $this->devtoolsPip = new DevtoolsPip($this->packageInstallationPlugin);
        if ($this->devtoolsPip->isUnsafe()) {
            $this->readBoolean('allowUnsafe', true);
            if ($this->parameters['allowUnsafe'] !== true) {
                throw new UserInputException('allowUnsafe');
            }
        }

        $targets = $this->devtoolsPip->getTargets($this->project);
        if (!\in_array($this->parameters['target'], $targets)) {
            throw new UserInputException('target');
        }
    }

    /**
     * Invokes a single PIP and returns the time needed to process it.
     *
     * @return array{invokeAgain: bool, pluginName: string, target: string, timeElapsed: string}
     */
    public function invoke()
    {
        $start = \microtime(true);

        $invokeAgain = new SyncPackageInstallationPlugin(
            $this->project,
            $this->devtoolsPip,
            $this->parameters['target'],
        )();

        return [
            'invokeAgain' => $invokeAgain,
            'pluginName' => $this->packageInstallationPlugin->pluginName,
            'target' => $this->parameters['target'],
            'timeElapsed' => WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.devtools.sync.status.success',
                ['timeElapsed' => \round(\microtime(true) - $start, 3)]
            ),
        ];
    }
}

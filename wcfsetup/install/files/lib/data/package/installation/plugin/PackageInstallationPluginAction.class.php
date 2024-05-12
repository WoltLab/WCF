<?php

namespace wcf\data\package\installation\plugin;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\option\OptionEditor;
use wcf\event\package\PackageInstallationPluginSynced;
use wcf\system\cache\CacheHandler;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\plugin\OptionPackageInstallationPlugin;
use wcf\system\package\SplitNodeException;
use wcf\system\search\SearchIndexManager;
use wcf\system\style\StyleHandler;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Executes package installation plugin-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  PackageInstallationPlugin       create()
 * @method  PackageInstallationPluginEditor[]   getObjects()
 * @method  PackageInstallationPluginEditor     getSingleObject()
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
     * @var DevtoolsPip
     */
    public $devtoolsPip;

    /**
     * @var PackageInstallationPlugin
     */
    public $packageInstallationPlugin;

    /**
     * @var DevtoolsProject
     */
    public $project;

    /**
     * Validates parameters to invoke a single PIP.
     *
     * @throws      PermissionDeniedException
     * @throws      UserInputException
     */
    public function validateInvoke()
    {
        if (!ENABLE_DEVELOPER_TOOLS || !WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')) {
            throw new PermissionDeniedException();
        }

        $this->readString('pluginName');
        $this->readInteger('projectID');
        $this->readString('target');

        $this->project = new DevtoolsProject($this->parameters['projectID']);
        if (!$this->project->projectID || $this->project->validate() !== '') {
            throw new UserInputException('projectID');
        }

        $this->packageInstallationPlugin = new PackageInstallationPlugin($this->parameters['pluginName']);
        if (!$this->packageInstallationPlugin->pluginName) {
            throw new UserInputException('pluginName');
        }

        $this->devtoolsPip = new DevtoolsPip($this->packageInstallationPlugin);
        $targets = $this->devtoolsPip->getTargets($this->project);
        if (!\in_array($this->parameters['target'], $targets)) {
            throw new UserInputException('target');
        }
    }

    /**
     * Invokes a single PIP and returns the time needed to process it.
     *
     * @return      string[]
     */
    public function invoke()
    {
        $dispatcher = new DevtoolsPackageInstallationDispatcher($this->project);
        /** @var IIdempotentPackageInstallationPlugin $pip */
        $pip = new $this->packageInstallationPlugin->className(
            $dispatcher,
            $this->devtoolsPip->getInstructions($this->project, $this->parameters['target'])
        );

        $start = \microtime(true);

        $invokeAgain = false;
        try {
            $pip->update();
        } catch (SplitNodeException $e) {
            if ($this->parameters['pluginName'] !== 'database') {
                throw new \RuntimeException("PIP '{$this->packageInstallationPlugin->pluginName}' is not allowed to throw a 'SplitNodeException'.");
            }

            $invokeAgain = true;
        }

        SearchIndexManager::getInstance()->createSearchIndices();

        VersionTracker::getInstance()->createStorageTables();

        CacheHandler::getInstance()->flushAll();

        if ($pip instanceof OptionPackageInstallationPlugin) {
            OptionEditor::resetCache();
        }

        switch ($this->packageInstallationPlugin->pluginName) {
            case 'file':
                StyleHandler::resetStylesheets(false);
                break;

            case 'language':
            case 'menuItem':
                LanguageFactory::getInstance()->clearCache();
                LanguageFactory::getInstance()->deleteLanguageCache();
                break;

            case 'acpTemplate':
            case 'template':
            case 'templateListener':
                // resets the compiled templates
                LanguageFactory::getInstance()->deleteLanguageCache();
                break;
        }

        EventHandler::getInstance()->fire(
            new PackageInstallationPluginSynced($this->packageInstallationPlugin->pluginName, $invokeAgain)
        );

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

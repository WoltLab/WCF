<?php

namespace wcf\command\devtools\project;

use wcf\command\package\RebuildBootstrapper;
use wcf\command\package\SetLastUpdateTime;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\option\OptionEditor;
use wcf\event\package\PackageInstallationPluginSynced;
use wcf\system\cache\builder\ApiEndpointCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\event\EventHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\package\plugin\DatabasePackageInstallationPlugin;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\package\plugin\OptionPackageInstallationPlugin;
use wcf\system\package\SplitNodeException;
use wcf\system\search\SearchIndexManager;
use wcf\system\style\StyleHandler;
use wcf\system\version\VersionTracker;

/**
 * Syncs a single target of a package installation plugin from a devtools
 * project into the installation.
 *
 * Returns `true` if the PIP has split its work and must be invoked again for
 * the same target.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SyncPackageInstallationPlugin
{
    public function __construct(
        private readonly DevtoolsProject $project,
        private readonly DevtoolsPip $devtoolsPip,
        private readonly string $target,
    ) {}

    public function __invoke(): bool
    {
        $packageInstallationPlugin = $this->devtoolsPip->getDecoratedObject();
        $pluginName = $packageInstallationPlugin->pluginName;

        $dispatcher = new DevtoolsPackageInstallationDispatcher($this->project);
        $instructions = $this->devtoolsPip->getInstructions($this->project, $this->target);
        if ($packageInstallationPlugin->className === DatabasePackageInstallationPlugin::class) {
            $pip = new DatabasePackageInstallationPlugin($dispatcher, $instructions, $this->project);
        } else {
            /** @var IPackageInstallationPlugin $pip */
            $pip = new $packageInstallationPlugin->className($dispatcher, $instructions);
        }

        $bootstrapPathname = \sprintf(
            '%slib/bootstrap/%s.php',
            \WCF_DIR,
            $this->project->getPackage()->package,
        );
        $hadBootstrapFile = \file_exists($bootstrapPathname);

        $invokeAgain = false;
        try {
            $pip->update();
        } catch (SplitNodeException $e) {
            if (!\in_array($pluginName, ['database', 'script'], true)) {
                throw new \RuntimeException("PIP '{$pluginName}' is not allowed to throw a 'SplitNodeException'.");
            }

            $invokeAgain = true;
        }

        SearchIndexManager::getInstance()->createSearchIndices();

        VersionTracker::getInstance()->createStorageTables();

        CacheHandler::getInstance()->flushAll();

        if ($pip instanceof OptionPackageInstallationPlugin) {
            OptionEditor::resetCache();
        }

        switch ($pluginName) {
            case 'file':
                ApiEndpointCacheBuilder::getInstance()->reset();
                StyleHandler::resetStylesheets(false);

                new SetLastUpdateTime()();

                if (!$hadBootstrapFile) {
                    \clearstatcache(filename: $bootstrapPathname);
                    if (\file_exists($bootstrapPathname)) {
                        new RebuildBootstrapper()();
                    }
                }
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
            new PackageInstallationPluginSynced($pluginName, $invokeAgain)
        );

        return $invokeAgain;
    }
}

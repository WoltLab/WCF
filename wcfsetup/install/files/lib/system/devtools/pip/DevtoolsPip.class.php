<?php

namespace wcf\system\devtools\pip;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\system\application\ApplicationHandler;
use wcf\system\package\plugin\DatabasePackageInstallationPlugin;
use wcf\system\package\plugin\IPackageInstallationPlugin;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Wrapper class for package installation plugins for use with the sync feature.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   PackageInstallationPlugin
 * @extends DatabaseObjectDecorator<PackageInstallationPlugin>
 */
class DevtoolsPip extends DatabaseObjectDecorator
{
    /**
     * PIPs that are not idempotent but may still be invoked on explicit request.
     * @since 6.3
     */
    private const UNSAFE_PIPS = ['script'];

    /**
     * project the pip object belongs to
     * @var ?DevtoolsProject
     * @since 5.2
     */
    protected $project;

    /**
     * package installation plugin object
     * @var ?IPackageInstallationPlugin
     * @since 5.2
     */
    protected $pip;

    /**
     * Walking the source tree is expensive, and the sync requests the same target
     * twice per request, from `invoke()` and from `DevtoolsInstaller::getTar()`.
     * @var array<string, array{0: array<string, array<string, string>|string>, 1: array<string, string>}>
     */
    private static array $archiveInstructions = [];

    /**
     * @inheritDoc
     */
    protected static $baseClass = PackageInstallationPlugin::class;

    /**
     * Returns true if the PIP class can be found.
     *
     * @return bool
     */
    public function classExists()
    {
        return \class_exists($this->getDecoratedObject()->className);
    }

    /**
     * Returns true if the PIP is expected to be idempotent.
     *
     * @return bool
     */
    public function isIdempotent()
    {
        return \is_subclass_of($this->getDecoratedObject()->className, IIdempotentPackageInstallationPlugin::class);
    }

    /**
     * Returns the default filename of this PIP.
     *
     * @return string
     */
    public function getDefaultFilename()
    {
        return \call_user_func([$this->getDecoratedObject()->className, 'getDefaultFilename']);
    }

    /**
     * @return string
     */
    public function getEffectiveDefaultFilename()
    {
        return './' . \preg_replace('~\.tar$~', '/', $this->getDefaultFilename());
    }

    /**
     * Returns true if the PIP exists, has a default filename and is idempotent.
     *
     * @return bool
     */
    public function isSupported()
    {
        return $this->classExists() && $this->getDefaultFilename() !== '' && $this->isIdempotent();
    }

    /**
     * Returns true if the PIP is not idempotent, but may be invoked on explicit
     * request. These PIPs are never part of a full sync.
     *
     * @since 6.3
     */
    public function isUnsafe(): bool
    {
        return $this->classExists()
            && !$this->isIdempotent()
            && \in_array($this->getDecoratedObject()->pluginName, self::UNSAFE_PIPS, true);
    }

    /**
     * Returns `true` if this pip supports adding and editing entries via a gui.
     *
     * @return bool
     * @since 5.2
     */
    public function supportsGui()
    {
        return $this->isSupported() && \is_subclass_of(
            $this->getDecoratedObject()->className,
            IGuiPackageInstallationPlugin::class
        );
    }

    /**
     * @return string|string[]
     */
    public function getSyncDependencies(bool $toJson = true)
    {
        $dependencies = \call_user_func([$this->getDecoratedObject()->className, 'getSyncDependencies']);

        return ($toJson) ? \json_encode($dependencies, \JSON_THROW_ON_ERROR) : $dependencies;
    }

    /**
     * Returns the project this object belongs to.
     *
     * @return DevtoolsProject
     * @since 5.2
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sets the project this object belongs to.
     *
     * @param DevtoolsProject $project
     * @return void
     * @since 5.2
     */
    public function setProject(DevtoolsProject $project)
    {
        $this->project = $project;
    }

    /**
     * Returns the package installation plugin object for this pip.
     *
     * Note: No target will be set for the package installation plugin object.
     *
     * @return IPackageInstallationPlugin|IGuiPackageInstallationPlugin
     * @since 5.2
     */
    public function getPip()
    {
        if ($this->pip === null) {
            $className = $this->getDecoratedObject()->className;

            $this->pip = new $className(
                new DevtoolsPackageInstallationDispatcher($this->getProject())
                // no target
            );
        }

        return $this->pip;
    }

    /**
     * Returns the first validation error.
     *
     * @return      string
     */
    public function getFirstError()
    {
        if (!$this->classExists()) {
            return WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.devtools.pip.error.className',
                ['className' => $this->getDecoratedObject()->className]
            );
        } elseif (!$this->isIdempotent()) {
            return WCF::getLanguage()->get('wcf.acp.devtools.pip.error.notIdempotent');
        } elseif ($this->getDefaultFilename() === '') {
            return WCF::getLanguage()->get('wcf.acp.devtools.pip.error.defaultFilename');
        }

        throw new \LogicException("Please call `isSupported()` to check for potential errors.");
    }

    /**
     * Returns the list of valid targets for this pip.
     *
     * @param DevtoolsProject $project
     * @return      string[]
     */
    public function getTargets(DevtoolsProject $project)
    {
        if ($this->isUnsafe()) {
            return match ($this->getDecoratedObject()->pluginName) {
                'script' => $this->getScriptTargets($project),
            };
        }

        if (!$this->isSupported()) {
            return [];
        }

        $path = $project->path;
        $defaultFilename = $this->getDefaultFilename();
        $targets = [];

        // the core uses a significantly different file layout
        if ($project->isCore()) {
            switch ($this->getDecoratedObject()->pluginName) {
                case 'acpTemplate':
                case 'file':
                case 'template':
                    // these pips are satisfied by definition
                    return [$defaultFilename];

                case 'database':
                    foreach (\glob("{$path}wcfsetup/install/files/{$defaultFilename}") as $file) {
                        $targets[] = \basename($file);
                    }

                    $targets[] = 'install_com.woltlab.wcf.php';

                    // `glob()` returns files in an arbitrary order
                    \sort($targets, \SORT_NATURAL);

                    return $targets;

                case 'language':
                    foreach (\glob($path . 'wcfsetup/install/lang/*.xml') as $file) {
                        $targets[] = \basename($file);
                    }

                    // `glob()` returns files in an arbitrary order
                    \sort($targets, \SORT_NATURAL);

                    return $targets;
            }

            if (\strpos($defaultFilename, '*') !== false) {
                foreach (\glob($path . 'com.woltlab.wcf/' . $defaultFilename) as $file) {
                    $targets[] = \basename($file);
                }

                // `glob()` returns files in an arbitrary order
                \sort($targets, \SORT_NATURAL);
            } else {
                if (\file_exists($path . 'com.woltlab.wcf/' . $defaultFilename)) {
                    $targets[] = $defaultFilename;
                }
            }
        } else {
            if (\preg_match('~^(?<filename>.*)\.tar$~', $defaultFilename, $match)) {
                if (\is_dir($path . $match['filename'])) {
                    $targets[] = $defaultFilename;
                }

                // check for application-specific pips too
                foreach (ApplicationHandler::getInstance()->getAbbreviations() as $abbreviation) {
                    if (\is_dir($path . $match['filename'] . '_' . $abbreviation)) {
                        $targets[] = $match['filename'] . "_{$abbreviation}.tar";
                    }
                }
            } else {
                if (\strpos($defaultFilename, '*') !== false) {
                    if ($this->pluginName === 'database') {
                        foreach (\glob("{$path}/files/{$defaultFilename}") as $file) {
                            $targets[] = \basename($file);
                        }
                        foreach (\glob("{$path}/files_wcf/{$defaultFilename}") as $file) {
                            $targets[] = \basename($file);
                        }
                    } else {
                        foreach (\glob($path . $defaultFilename) as $file) {
                            $targets[] = \basename($file);
                        }
                    }

                    // `glob()` returns files in an arbitrary order
                    \sort($targets, \SORT_NATURAL);
                } else {
                    if (\file_exists($path . $defaultFilename)) {
                        $targets[] = $defaultFilename;
                    }
                }
            }
        }

        return $targets;
    }

    /**
     * Returns the install and update scripts of the project, relative to the
     * project's `files*` directory for regular packages. The Core only offers
     * its update scripts.
     *
     * @return string[]
     * @since 6.3
     */
    private function getScriptTargets(DevtoolsProject $project): array
    {
        if ($project->isCore()) {
            // The install scripts of the Core set up the installation itself
            // and must never run against an existing one.
            $types = ['update'];
            $directories = ['' => "{$project->path}wcfsetup/install/files/"];
        } else {
            $types = ['install', 'update'];
            $directories = ['files/' => "{$project->path}files/"];
            foreach (ApplicationHandler::getInstance()->getAbbreviations() as $abbreviation) {
                $directories["files_{$abbreviation}/"] = "{$project->path}files_{$abbreviation}/";
            }
        }

        $targets = [];
        foreach ($directories as $prefix => $directory) {
            foreach ($types as $type) {
                foreach (\glob("{$directory}acp/{$type}_*.php") as $file) {
                    $targets[] = "{$prefix}acp/" . \basename($file);
                }
            }
        }

        // `glob()` returns files in an arbitrary order
        \sort($targets, \SORT_NATURAL);

        return $targets;
    }

    /**
     * Computes and prepares the instructions for the provided target file.
     *
     * @return array<string, array<string, string>|string>
     */
    public function getInstructions(DevtoolsProject $project, string $target)
    {
        $pluginName = $this->getDecoratedObject()->pluginName;
        if (!\in_array($pluginName, ['acpTemplate', 'file', 'template'], true)) {
            return $this->readInstructions($project, $target);
        }

        $tar = $project->getPackageArchive()->getTar();
        $key = "{$project->projectID}-{$pluginName}-{$target}";
        if (isset(self::$archiveInstructions[$key])) {
            [$instructions, $files] = self::$archiveInstructions[$key];
            $tar->reset();
            $tar->setFiles($files);
        } else {
            $instructions = $this->readInstructions($project, $target);
            self::$archiveInstructions[$key] = [$instructions, $tar->getFiles()];
        }

        return $instructions;
    }

    /**
     * @return array<string, array<string, string>|string>
     */
    private function readInstructions(DevtoolsProject $project, string $target): array
    {
        $defaultFilename = $this->getDefaultFilename();
        $pluginName = $this->getDecoratedObject()->pluginName;
        $tar = $project->getPackageArchive()->getTar();
        $tar->reset();

        $instructions = [
            'value' => $target,
        ];

        if ($project->isCore()) {
            switch ($pluginName) {
                case 'acpTemplate':
                case 'file':
                case 'template':
                    if ($pluginName === 'acpTemplate' || $pluginName === 'template') {
                        $path = ($pluginName === 'acpTemplate') ? 'wcfsetup/install/files/acp/templates/' : 'com.woltlab.wcf/templates/';
                        foreach (\glob($project->path . $path . '*.tpl') as $template) {
                            $tar->registerFile(\basename($template), FileUtil::unifyDirSeparator($template));
                        }
                    } else {
                        $path = 'wcfsetup/install/files/';
                        $absolutePath = FileUtil::getRealPath($project->path . $path);

                        $directory = new \RecursiveDirectoryIterator($absolutePath);
                        $filter = new \RecursiveCallbackFilterIterator($directory, static function ($current) use ($absolutePath) {
                            /** @var \SplFileInfo $current */
                            $filename = $current->getFilename();
                            if ($filename[0] === '.' && $filename !== '.gitignore' && $filename !== '.htaccess') {
                                // ignore dot files and files/directories starting with a dot
                                return false;
                            } elseif ($filename === 'options.inc.php') {
                                // ignores `options.inc.php` file which is only valid for installation
                                return false;
                            } elseif ($filename === 'app.config.inc.php') {
                                // ignores `app.config.inc.php` file which has a dummy contents for installation
                                // and cannot be restored by WSC itself
                                return false;
                            } elseif ($filename === 'require.build.js') {
                                // ignore require build configuration file
                                return false;
                            } elseif ($filename === 'templates') {
                                $currentPath = FileUtil::addTrailingSlash($current->getPath());

                                // Ignore the template directory at the root of the core.
                                if (\str_ends_with($currentPath, $absolutePath)) {
                                    return false;
                                }

                                // Also exclude the ACP template directory.
                                if (\str_ends_with($currentPath, FileUtil::addTrailingSlash($absolutePath . 'acp'))) {
                                    return false;
                                }
                            }

                            return true;
                        });

                        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $item) {
                            /** @var \SplFileInfo $item */
                            $itemPath = $item->getRealPath();
                            if (\is_dir($itemPath)) {
                                continue;
                            }

                            $tar->registerFile(
                                FileUtil::getRelativePath(
                                    $project->path . $path,
                                    $item->getPath()
                                ) . $item->getFilename(),
                                $itemPath
                            );
                        }
                    }

                    break;

                case 'database':
                    if ($target === 'install_com.woltlab.wcf.php') {
                        $instructions['value'] = $target;
                    } else {
                        $instructions['value'] = DatabasePackageInstallationPlugin::SCRIPT_DIR . $target;

                        $tar->registerFile($instructions['value'], $project->path . 'wcfsetup/install/files/' . $target);
                    }

                    break;

                case 'language':
                    $tar->registerFile($target, $project->path . 'wcfsetup/install/lang/' . $target);

                    break;

                case 'script':
                    // The script is included from the application directory,
                    // it must have been synced through the `file` PIP before.
                    break;

                default:
                    $tar->registerFile($target, $project->path . 'com.woltlab.wcf/' . $target);

                    break;
            }
        } else {
            switch ($pluginName) {
                case 'acpTemplate':
                case 'file':
                case 'template':
                    if ($pluginName === 'acpTemplate' || $pluginName === 'template') {
                        $pathPrefix = ($pluginName === 'acpTemplate') ? 'acptemplates' : 'templates';

                        if (\preg_match('~^' . $pathPrefix . '_(?<application>.*)\.tar$~', $target, $match)) {
                            $path = "{$pathPrefix}_{$match['application']}/";

                            $instructions['attributes'] = ['application' => $match['application']];
                        } else {
                            $path = "{$pathPrefix}/";
                        }

                        foreach (\glob($project->path . $path . '*.tpl') as $template) {
                            $tar->registerFile(\basename($template), FileUtil::unifyDirSeparator($template));
                        }
                    } else {
                        $path = 'files/';
                        if (\preg_match('~^files_(?<application>.*)\.tar$~', $target, $match)) {
                            $path = "files_{$match['application']}/";

                            $instructions['attributes'] = ['application' => $match['application']];
                        }

                        $directory = new \RecursiveDirectoryIterator($project->path . $path);
                        $filter = new \RecursiveCallbackFilterIterator($directory, static function ($current) {
                            /** @var \SplFileInfo $current */
                            $filename = $current->getFilename();
                            if ($filename[0] === '.' && $filename !== '.gitignore' && $filename !== '.htaccess') {
                                // ignore dot files and files/directories starting with a dot
                                return false;
                            } elseif ($filename === 'require.build.js') {
                                // ignore require build configuration file
                                return false;
                            }

                            return true;
                        });

                        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $item) {
                            /** @var \SplFileInfo $item */
                            $itemPath = $item->getRealPath();
                            if (\is_dir($itemPath)) {
                                continue;
                            }

                            $tar->registerFile(
                                FileUtil::getRelativePath(
                                    $project->path . $path,
                                    $item->getPath()
                                ) . $item->getFilename(),
                                $itemPath
                            );
                        }
                    }

                    break;

                case 'database':
                    $instructions['value'] = DatabasePackageInstallationPlugin::SCRIPT_DIR . $target;

                    $path = "{$project->path}files/{$instructions['value']}";
                    if (!\is_file($path)) {
                        $path = "{$project->path}files_wcf/{$instructions['value']}";

                        $instructions['attributes'] = [
                            'application' => 'wcf',
                        ];
                    }

                    $tar->registerFile($instructions['value'], $path);

                    break;

                case 'script':
                    [$directory, $instructions['value']] = \explode('/', $target, 2);
                    if (\str_starts_with($directory, 'files_')) {
                        $instructions['attributes'] = [
                            'application' => \substr($directory, \strlen('files_')),
                        ];
                    }

                    break;

                default:
                    if (\strpos($defaultFilename, '*') !== false) {
                        $tar->registerFile(
                            $target,
                            $project->path . \preg_replace('~\*.*$~', $target, $defaultFilename)
                        );
                    } else {
                        $tar->registerFile($target, $project->path . $target);
                    }

                    break;
            }
        }

        return $instructions;
    }

    /**
     * Indicates that this is an important PIP that should be listed
     * at the top on the project sync page.
     *
     * @since 5.5
     */
    public function isImportant(): bool
    {
        return \in_array($this->getDecoratedObject()->pluginName, ['acpTemplate', 'file', 'language', 'template'], true);
    }
}

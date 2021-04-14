<?php

namespace wcf\system\devtools\package;

use wcf\system\package\PackageArchive;
use wcf\system\package\plugin\ACPTemplatePackageInstallationPlugin;
use wcf\system\package\plugin\FilePackageInstallationPlugin;
use wcf\system\package\plugin\TemplatePackageInstallationPlugin;
use wcf\util\FileUtil;

/**
 * Specialized implementation to emulate a regular package installation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Devtools\Package
 * @since       3.1
 *
 * @method  DevtoolsTar getTar()
 */
class DevtoolsPackageArchive extends PackageArchive
{
    protected $packageXmlPath = '';

    /** @noinspection PhpMissingParentConstructorInspection @inheritDoc */
    public function __construct($packageXmlPath)
    {
        $this->packageXmlPath = $packageXmlPath;
    }

    /**
     * @inheritDoc
     */
    public function openArchive()
    {
        if ($this->tar) {
            return;
        }

        $projectDir = FileUtil::addTrailingSlash(
            FileUtil::unifyDirSeparator(\realpath(\dirname($this->packageXmlPath)))
        );

        $it = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($projectDir),
                static function (\SplFileInfo $current, string $key, \RecursiveDirectoryIterator $it): bool {
                    // Skip '.' and '..'.
                    if ($it->isDot()) {
                        return false;
                    }

                    // Skip hidden files.
                    if ($current->getFilename()[0] === '.') {
                        return false;
                    }

                    if ($current->isDir()) {
                        // Check if we are in the project root.
                        if ($it->getSubPath() === '') {
                            // Skip acptemplates / files / templates.
                            if (
                                \preg_match(
                                    '/^(acptemplates|files|templates)(_[a-z0-9]+)?$/',
                                    $current->getFilename()
                                )
                            ) {
                                return false;
                            }

                            // Skip node_modules and vendor.
                            if (\preg_match('/^(node_modules|vendor)$/', $current->getFilename())) {
                                return false;
                            }

                            // Skip TypeScript source files.
                            if (\preg_match('/^ts$/', $current->getFilename())) {
                                return false;
                            }
                        }

                        return true;
                    }

                    return true;
                }
            )
        );
        $readFiles = \array_map(static function (\SplFileInfo $f): string {
            return $f->getPathname();
        }, \iterator_to_array($it));

        $files = [];
        foreach ($readFiles as $file) {
            if (\is_file($file)) {
                $files[\str_replace($projectDir, '', $file)] = $file;
            }
        }

        $this->tar = new DevtoolsTar($files);

        $this->readPackageInfo();
        foreach ($this->getInstallInstructions() as $instruction) {
            $archive = null;
            switch ($instruction['pip']) {
                case 'acpTemplate':
                    $archive = $instruction['value'] ?: ACPTemplatePackageInstallationPlugin::getDefaultFilename();
                    break;

                case 'file':
                    $archive = $instruction['value'] ?: FilePackageInstallationPlugin::getDefaultFilename();
                    break;

                case 'template':
                    $archive = $instruction['value'] ?: TemplatePackageInstallationPlugin::getDefaultFilename();
                    break;
            }

            if ($archive !== null) {
                $this->tar->registerFile($archive, $projectDir . $archive);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function extractTar($filename, $tempPrefix = 'package_')
    {
        return $filename;
    }
}

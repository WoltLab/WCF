<?php

namespace wcf\data\devtools\project;

use wcf\data\DatabaseObject;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\data\package\installation\plugin\PackageInstallationPluginList;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\devtools\package\DevtoolsPackageArchive;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Represents a devtools project.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @property-read   int $projectID  unique id of the project
 * @property-read   string $name       internal name for display inside the ACP
 * @property-read   string $path       file system path
 */
class DevtoolsProject extends DatabaseObject
{
    /**
     * is `true` if it has already been attempted to fetch a package
     * @var     bool
     * @since   5.2
     */
    protected $didFetchPackage = false;

    /**
     * @var bool
     */
    protected $isCore;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @var DevtoolsPackageArchive
     */
    protected $packageArchive;

    /**
     * @var PackageValidationException
     * @since   5.4
     */
    protected $packageValidationException;

    /**
     * Returns a list of decorated PIPs.
     *
     * @return      DevtoolsPip[]
     */
    public function getPips()
    {
        $pipList = new PackageInstallationPluginList();
        $pipList->readObjects();

        $pips = \array_map(function (PackageInstallationPlugin $pip) {
            $devtoolsPip = new DevtoolsPip($pip);
            $devtoolsPip->setProject($this);

            return $devtoolsPip;
        }, $pipList->getObjects());

        \uasort($pips, static function (DevtoolsPip $a, DevtoolsPip $b) {
            if ($a->isImportant() === $b->isImportant()) {
                return \strcasecmp($a->pluginName, $b->pluginName);
            }

            return $a->isImportant() ? -1 : 1;
        });

        return $pips;
    }

    /**
     * Validates the repository and returns the first error message, or
     * an empty string on success.
     *
     * @param bool $pathOnly
     * @return      string
     */
    public function validate($pathOnly = false)
    {
        $errorType = self::validatePath($this->path);
        if ($errorType !== '') {
            return WCF::getLanguage()->get('wcf.acp.devtools.project.path.error.' . $errorType);
        } elseif ($pathOnly) {
            return '';
        }

        return $this->validatePackageXml();
    }

    /**
     * Returns true if this project appears to be `WoltLab Suite Core`.
     *
     * @return      bool
     */
    public function isCore()
    {
        if ($this->isCore === null) {
            $this->isCore = self::pathIsCore($this->path);
        }

        return $this->isCore;
    }

    /**
     * Returns the path to the project's `package.xml` file.
     *
     * @return  string
     * @since   5.2
     */
    public function getPackageXmlPath()
    {
        return $this->path . ($this->isCore() ? 'com.woltlab.wcf/' : '') . 'package.xml';
    }

    /**
     * Validates the package.xml and checks if the package is already installed.
     *
     * @return      string
     */
    public function validatePackageXml()
    {
        // Make sure that the package archive is read and any validation exception while opening the
        // archive is caught.
        $this->getPackageArchive();
        if ($this->packageValidationException) {
            return $this->packageValidationException->getErrorMessage();
        }

        if ($this->getPackage() === null) {
            return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.notInstalled', [
                'project' => $this,
            ]);
        }

        $normalizeVersion = static function ($version) {
            return \preg_replace('~^(\d+)\.(\d+)\..*$~', '\\1.\\2', $version);
        };

        if ($normalizeVersion($this->packageArchive->getPackageInfo('version')) !== $normalizeVersion($this->package->packageVersion)) {
            return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.path.error.versionMismatch', [
                'version' => $this->packageArchive->getPackageInfo('version'),
                'packageVersion' => $this->package->packageVersion,
            ]);
        }

        return '';
    }

    /**
     * @return      Package
     */
    public function getPackage()
    {
        if ($this->package === null) {
            $packageList = new PackageList();
            $packageList->getConditionBuilder()->add(
                'package = ?',
                [$this->getPackageArchive()->getPackageInfo('name')]
            );
            $packageList->readObjects();

            if (\count($packageList)) {
                $this->package = $packageList->current();
            }

            $this->didFetchPackage = true;
        }

        return $this->package;
    }

    /**
     * @return      DevtoolsPackageArchive
     */
    public function getPackageArchive()
    {
        if ($this->packageArchive === null) {
            $this->packageArchive = new DevtoolsPackageArchive($this->getPackageXmlPath());

            try {
                $this->packageArchive->openArchive();
            } catch (PackageValidationException $e) {
                $this->packageValidationException = $e;
            }
        }

        return $this->packageArchive;
    }

    /**
     * Returns the absolute paths of the language files.
     *
     * @return  string[]
     */
    public function getLanguageFiles()
    {
        $languageDirectory = $this->path . ($this->isCore() ? 'wcfsetup/install/lang/' : 'language/');

        if (!\is_dir($languageDirectory)) {
            return [];
        }

        return \array_values(DirectoryUtil::getInstance($languageDirectory)->getFiles(
            \SORT_ASC,
            Regex::compile('\w+\.xml')
        ));
    }

    /**
     * Sets the package that belongs to this project.
     *
     * @param Package $package
     * @throws  \InvalidArgumentException   if the identifier of the given package does not match
     * @since   5.2
     */
    public function setPackage(Package $package)
    {
        if ($package->package !== $this->getPackageArchive()->getPackageInfo('name')) {
            throw new \InvalidArgumentException("Package identifier of given package ('{$package->package}') does not match ('{$this->packageArchive->getPackageInfo('name')}')");
        }

        $this->package = $package;
    }

    /**
     * @since 6.1
     */
    public function hasFailedPackageXmlValidation(): bool
    {
        return $this->packageValidationException !== null;
    }

    /**
     * Validates the provided path and returns an error code
     * if the path does not exist (`notFound`) or if there is
     * no package.xml (`packageXml`).
     *
     * @param string $path
     * @return      string
     */
    public static function validatePath($path)
    {
        if (!\is_dir($path)) {
            return 'notFound';
        } elseif (!\file_exists($path . 'package.xml')) {
            // check if this is `com.woltlab.wcf`
            if (!self::pathIsCore($path)) {
                return 'packageXml';
            }
        }

        return '';
    }

    /**
     * Returns true if the path appears to point to `WoltLab Suite Core`.
     *
     * @param string $path
     * @return      bool
     */
    public static function pathIsCore($path)
    {
        return \is_dir($path . 'com.woltlab.wcf') && \file_exists($path . 'com.woltlab.wcf/package.xml');
    }
}

<?php

namespace wcf\data\package;

use wcf\acp\page\PackagePage;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents a package.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $packageID      unique id of the package
 * @property-read   string $package        unique textual identifier of the package
 * @property-read   string $packageDir     relative directory to Core in which the application is installed or empty if package is no application or Core
 * @property-read   string $packageName        name of the package or name of language item which contains the name
 * @property-read   string $packageDescription description of the package or name of language item which contains the description
 * @property-read   string $packageVersion     installed version of package
 * @property-read   int $packageDate        timestamp at which the installed package version has been released
 * @property-read   int $installDate        timestamp at which the package has been installed
 * @property-read   int $updateDate     timestamp at which the package has been updated or installed if it has not been updated yet
 * @property-read   string $packageURL     external url to website with more information about the package
 * @property-read   int $isApplication      is `1` if the package delivers an application, otherwise `0`
 * @property-read   string $author         author of the package
 * @property-read   string $authorURL      external url to the website of the package author
 */
class Package extends DatabaseObject implements ILinkableObject, IRouteController
{
    /**
     * recursive list of packages that were given as required packages during installation
     * @var     Package[]
     * @since   5.2
     */
    protected $allRequiredPackages;

    /**
     * list of packages that this package requires
     * @var Package[]
     */
    protected $dependencies;

    /**
     * list of packages that require this package
     * @var Package[]
     */
    protected $dependentPackages;

    /**
     * installation directory
     * @var string
     */
    protected $dir = '';

    /**
     * list of packages that were given as required packages during installation
     * @var Package[]
     */
    protected $requiredPackages;

    /**
     * list of ids of packages which are required by another package
     * @var int[]
     */
    protected static $requiredPackageIDs;

    /**
     * package requirements
     * @var array
     */
    protected static $requirements;

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(PackagePage::class, [
            'object' => $this,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->getName();
    }

    /**
     * Returns true if this package is required by other packages.
     *
     * @return  bool
     */
    public function isRequired()
    {
        self::loadRequirements();

        return \in_array($this->packageID, self::$requiredPackageIDs);
    }

    /**
     * Returns true if package is a plugin.
     *
     * @return  bool
     */
    public function isPlugin()
    {
        if ($this->isApplication) {
            return false;
        }

        return true;
    }

    /**
     * Returns the name of this package.
     *
     * @return  string
     */
    public function getName()
    {
        return WCF::getLanguage()->get($this->packageName);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Returns the description of this package in the active user's language.
     *
     * @return  string
     * @since   5.2
     */
    public function getDescription()
    {
        return WCF::getLanguage()->get($this->packageDescription);
    }

    /**
     * Returns the abbreviation of the package name.
     *
     * @param string $package
     * @return  string
     */
    public static function getAbbreviation($package)
    {
        $array = \explode('.', $package);

        return \array_pop($array);
    }

    /**
     * Returns the list of packages which are required by this package. The
     * returned packages are the packages given in the <requiredpackages> tag
     * in the package.xml of this package.
     *
     * @return  Package[]
     */
    public function getRequiredPackages()
    {
        if ($this->requiredPackages === null) {
            self::loadRequirements();

            $this->requiredPackages = [];
            if (isset(self::$requirements[$this->packageID])) {
                foreach (self::$requirements[$this->packageID] as $packageID) {
                    $this->requiredPackages[$packageID] = PackageCache::getInstance()->getPackage($packageID);
                }
            }
        }

        return $this->requiredPackages;
    }

    /**
     * Returns the recursive list of packages which are required by this package.
     * The returned packages are the packages given in the <requiredpackages> tag
     * in the package.xml of this package and recursively repeats that for all of
     * those required packages.
     *
     * @return  Package[]
     * @since   5.2
     */
    public function getAllRequiredPackages()
    {
        if ($this->allRequiredPackages === null) {
            $this->allRequiredPackages = $this->getRequiredPackages();
            $packagesToCheck = $this->allRequiredPackages;

            /** @var Package $checkedPackage */
            while (($checkedPackage = \array_pop($packagesToCheck))) {
                $newRequiredPackages = \array_diff($checkedPackage->getRequiredPackages(), $this->allRequiredPackages);

                $this->allRequiredPackages += $newRequiredPackages;
                $packagesToCheck = \array_merge($packagesToCheck, $newRequiredPackages);
            }
        }

        return $this->allRequiredPackages;
    }

    /**
     * Returns true if current user can uninstall this package.
     *
     * @return  bool
     */
    public function canUninstall()
    {
        if (!WCF::getSession()->getPermission('admin.configuration.package.canUninstallPackage')) {
            return false;
        }

        // disallow uninstallation of WCF
        if ($this->package == 'com.woltlab.wcf') {
            return false;
        }

        // check if package is required by another package
        if ($this->isRequired()) {
            return false;
        }

        return true;
    }

    /**
     * Returns a list of packages dependent from current package.
     *
     * @return  Package[]
     */
    public function getDependentPackages()
    {
        if ($this->dependentPackages === null) {
            self::loadRequirements();

            $this->dependentPackages = [];
            foreach (self::$requirements as $packageID => $requiredPackageIDs) {
                if (\in_array($this->packageID, $requiredPackageIDs)) {
                    $this->dependentPackages[$packageID] = PackageCache::getInstance()->getPackage($packageID);
                }
            }
        }

        return $this->dependentPackages;
    }

    /**
     * Overwrites current package version.
     *
     * DO NOT call this method outside the package installation!
     *
     * @param string $packageVersion
     */
    public function setPackageVersion($packageVersion)
    {
        $this->data['packageVersion'] = $packageVersion;
    }

    /**
     * Returns the absolute path to the package directory with a trailing slash.
     *
     * @return  string
     * @since   3.1
     */
    public function getAbsolutePackageDir()
    {
        return FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $this->packageDir));
    }

    /**
     * Loads package requirements.
     */
    protected static function loadRequirements()
    {
        if (self::$requirements === null) {
            $sql = "SELECT  packageID, requirement
                    FROM    wcf1_package_requirement";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();

            self::$requiredPackageIDs = [];
            self::$requirements = [];
            while ($row = $statement->fetchArray()) {
                if (!isset(self::$requirements[$row['packageID']])) {
                    self::$requirements[$row['packageID']] = [];
                }

                self::$requirements[$row['packageID']][] = $row['requirement'];

                if (!\in_array($row['requirement'], self::$requiredPackageIDs)) {
                    self::$requiredPackageIDs[] = $row['requirement'];
                }
            }
        }
    }

    /**
     * Returns true if package identified by $package is already installed.
     *
     * @param string $package
     * @return  bool
     */
    public static function isAlreadyInstalled($package)
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_package
                WHERE   package = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$package]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Checks if a package name is valid.
     *
     * A valid package name begins with at least one alphanumeric character
     * or an underscore, followed by a dot, followed by at least one alphanumeric
     * character or an underscore and the same again, possibly repeatedly.
     * The package name cannot be any longer than 191 characters in total due to
     * internal database character encoding limitations.
     * Example:
     *  com.woltlab.wcf
     *
     * Reminder: The package name being examined here contains the 'name' attribute
     * of the 'package' tag noted in the 'package.xml' file delivered inside
     * the respective package.
     */
    public static function isValidPackageName(string $packageName): bool
    {
        if (\mb_strlen($packageName) < 3 || \mb_strlen($packageName) > 191) {
            return false;
        }

        return !!\preg_match('/^[a-zA-Z0-9_-]+\\.[a-zA-Z0-9_-]+(\\.[a-zA-Z0-9_-]+)+$/', $packageName);
    }

    /**
     * Returns true if package version is valid.
     *
     * Examples of valid package versions:
     *  1.0.0
     *  4.0.0 Alpha 1
     *  3.1.7 RC 4
     */
    public static function isValidVersion(string $version): bool
    {
        return !!\preg_match('/^([0-9]+)\\.([0-9]+)\\.([0-9]+)( (dev|Alpha|Beta|RC) ([0-9]+))?$/', $version);
    }

    /**
     * Checks the version number of the installed package against the "fromversion"
     * number of the update.
     *
     * The "fromversion" number may contain wildcards (asterisks) which means
     * that the update covers the whole range of release numbers where the asterisk
     * wildcards digits from 0 to 9.
     * For example, if "fromversion" is "1.1.*" and this package updates to
     * version 1.2.0, all releases from 1.1.0 to 1.1.9 may be updated using
     * this package.
     */
    public static function checkFromversion(string $currentVersion, string $fromVersion): bool
    {
        if (\str_contains($fromVersion, '*')) {
            // from version with wildcard
            // use regular expression
            $fromVersion = \str_replace('\*', '.*', \preg_quote($fromVersion, '!'));
            if (\preg_match('!^' . $fromVersion . '$!i', $currentVersion)) {
                return true;
            }
        } else {
            if (self::compareVersion($currentVersion, $fromVersion, '=')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compares two version number strings.
     *
     * @param string $operator
     * @return  bool        result
     * @see http://www.php.net/manual/en/function.version-compare.php
     */
    public static function compareVersion(string $version1, string $version2, $operator = null)
    {
        $version1 = self::formatVersionForCompare($version1);
        $version2 = self::formatVersionForCompare($version2);
        if ($operator === null) {
            return \version_compare($version1, $version2);
        } else {
            return \version_compare($version1, $version2, $operator);
        }
    }

    /**
     * Formats a package version string for comparing.
     *
     * @see     http://www.php.net/manual/en/function.version-compare.php
     */
    private static function formatVersionForCompare(string $version): string
    {
        // remove spaces
        $version = \str_replace(' ', '', $version);

        // correct special version strings
        $version = \str_ireplace('dev', 'dev', $version);
        $version = \str_ireplace('alpha', 'alpha', $version);
        $version = \str_ireplace('beta', 'beta', $version);
        $version = \str_ireplace('RC', 'RC', $version);

        return \str_ireplace('pl', 'pl', $version);
    }

    /**
     * Writes the config.inc.php for an application.
     *
     * @param int $packageID
     */
    public static function writeConfigFile($packageID)
    {
        $package = new self($packageID);
        $packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $package->packageDir));

        $prefix = \strtoupper(self::getAbbreviation($package->package));

        $content = "<?php\n";
        $content .= "// {$package->package} (packageID {$packageID})\n";
        $content .= "if (!defined('{$prefix}_DIR')) define('{$prefix}_DIR', __DIR__.'/');\n";
        $content .= "if (!defined('PACKAGE_ID')) define('PACKAGE_ID', {$packageID});\n";

        if ($packageID != 1) {
            $content .= "\n";
            $content .= "// helper constants for applications\n";
            $content .= "if (!defined('RELATIVE_{$prefix}_DIR')) define('RELATIVE_{$prefix}_DIR', '');\n";
            $content .= "if (!defined('RELATIVE_WCF_DIR')) define('RELATIVE_WCF_DIR', RELATIVE_{$prefix}_DIR.'" . FileUtil::getRelativePath(
                $packageDir,
                WCF_DIR
            ) . "');\n";
        }

        \file_put_contents($packageDir . PackageInstallationDispatcher::CONFIG_FILE, $content);
    }
}

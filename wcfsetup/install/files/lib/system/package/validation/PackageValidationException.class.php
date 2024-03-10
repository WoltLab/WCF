<?php

namespace wcf\system\package\validation;

use wcf\system\exception\SystemException;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;

/**
 * Represents exceptions occurred during validation of a package archive. This exception
 * does not cause the details to be logged.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageValidationException extends SystemException
{
    /**
     * list of additional details for each subtype
     * @var string[]
     */
    protected $details = [];

    /**
     * missing archive, expects the detail 'archive' and optionally 'targetArchive'
     * (extracting archive from the archive)
     * @var int
     */
    const FILE_NOT_FOUND = 1;

    /**
     * missing package.xml, expects the detail 'archive'
     * @var int
     */
    const MISSING_PACKAGE_XML = 2;

    /**
     * package name violates WCF's schema, expects the detail 'packageName'
     * @var int
     */
    const INVALID_PACKAGE_NAME = 3;

    /**
     * package version violates WCF's schema, expects the detail 'packageVersion'
     * @var int
     */
    const INVALID_PACKAGE_VERSION = 4;

    /**
     * package contains no install instructions and an update is not possible, expects the detail 'packageName'
     * @var int
     */
    const NO_INSTALL_PATH = 5;

    /**
     * package is already installed and cannot be updated using current archive, expects the
     * details 'packageName', 'packageVersion' and 'deliveredPackageVersion'
     * @var int
     */
    const NO_UPDATE_PATH = 6;

    /**
     * packages which exclude the current package, expects the detail 'packages' (list of \wcf\data\package\Package)
     * @var int
     */
    const EXCLUDING_PACKAGES = 7;

    /**
     * packages which are excluded by current package, expects the detail 'packages' (list of \wcf\data\package\Package)
     * @var int
     */
    const EXCLUDED_PACKAGES = 8;

    /**
     * package version is lower than the request version, expects the
     * details 'packageName', 'packageVersion' and 'deliveredPackageVersion'
     * @var int
     */
    const INSUFFICIENT_VERSION = 9;

    /**
     * requirement is set but neither installed nor provided, expects the
     * details 'packageName', 'packageVersion' and 'package' (must be
     * an instance of \wcf\data\package\Package or null if not installed)
     * @var int
     */
    const MISSING_REQUIREMENT = 10;

    /**
     * file reference for a package installation plugin is missing, expects the details 'pip', 'type' and 'value'
     * @var int
     */
    const MISSING_INSTRUCTION_FILE = 11;

    /**
     * the uploaded version is already installed, expects the details 'packageName' and 'packageVersion'
     * @var int
     */
    const ALREADY_INSTALLED = 12;

    /**
     * the provided API version string is invalid and does not fall into the range from `2017` through `2099`
     * @var int
     * @deprecated 5.2
     */
    const INVALID_API_VERSION = 13;

    /**
     * the package is not compatible with the current API version or any other of the supported ones
     * @var int
     * @deprecated 5.2
     */
    const INCOMPATIBLE_API_VERSION = 14;

    /**
     * the package lacks any sort of API compatibility data
     * @var int
     * @deprecated 5.2
     */
    const MISSING_API_VERSION = 15;

    /**
     * the void is not the only instruction
     * @var int
     */
    const VOID_NOT_ALONE = 16;

    /**
     * the void is used during installation
     * @var int
     */
    const VOID_ON_INSTALL = 17;

    /**
     * an app with the same abbreviation is already installed
     * @since   5.4
     */
    const DUPLICATE_ABBREVIATION = 18;

    /**
     * the version of an excluded package is invalid
     * @var int
     * @since 5.5
     */
    const INVALID_EXCLUDED_PACKAGE_VERSION_NUMBER = 19;

    /**
     * the package excludes itself
     * @var int
     * @since   5.5
     */
    const SELF_EXCLUDE = 20;

    /**
     * the package does not explicitly require com.woltlab.wcf
     * @var int
     * @since   6.0
     */
    const MISSING_COM_WOLTLAB_WCF_REQUIREMENT = 21;

    /**
     * the package requires com.woltlab.wcf in an ancient version
     * @var int
     * @since   6.0
     */
    const ANCIENT_COM_WOLTLAB_WCF_REQUIREMENT = 22;

    /**
     * the version of a required package is invalid
     * @var int
     * @since 6.0
     */
    const INVALID_REQUIRED_PACKAGE_VERSION_NUMBER = 23;

    /**
     * an unknown tag is given in the <packageinformation>
     * @var int
     * @since 6.0
     */
    const UNKNOWN_PACKAGE_INFORMATION = 24;

    /**
     * an unknown tag is given in the <authorinformation>
     * @var int
     * @since 6.0
     */
    const UNKNOWN_AUTHOR_INFORMATION = 25;

    /**
     * no author information is given
     * @var int
     * @since 6.0
     */
    const MISSING_AUTHOR_INFORMATION = 26;

    /**
     * no display name is given
     * @var int
     * @since 6.0
     */
    const MISSING_DISPLAY_NAME = 27;

    /**
     * an duplicate tag is given in the <packageinformation>
     * @var int
     * @since 6.0
     */
    const DUPLICATE_PACKAGE_INFORMATION = 28;

    /**
     * no version information is given
     * @var int
     * @since 6.0
     */
    const MISSING_PACKAGE_VERSION = 29;

    /**
     * no release date is given
     * @var int
     * @since 6.0
     */
    const MISSING_PACKAGE_DATE = 30;

    /**
     * the `package.xml` has syntax errors
     * @var int
     * @since 6.0
     */
    const INVALID_PACKAGE_XML = 31;

    /**
     * Creates a new PackageArchiveValidationException.
     *
     * @param int $code
     * @param string[] $details
     */
    public function __construct($code, array $details = [])
    {
        $this->details = $details;

        parent::__construct($this->getLegacyMessage($code), $code);
    }

    /**
     * Returns exception details.
     *
     * @return  string[]
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Returns the readable error message.
     *
     * @param int $code
     * @return  string
     */
    public function getErrorMessage($code = null)
    {
        if (!empty($this->details['legacyMessage'])) {
            return $this->details['legacyMessage'];
        }

        return WCF::getLanguage()->getDynamicVariable(
            'wcf.acp.package.validation.errorCode.' . ($code === null ? $this->getCode() : $code),
            $this->getDetails()
        );
    }

    /**
     * Returns legacy error messages to mimic WCF 2.0.x PackageArchive's exceptions.
     *
     * @param int $code
     * @return  string
     */
    protected function getLegacyMessage($code)
    {
        switch ($code) {
            case self::FILE_NOT_FOUND:
                if (isset($this->details['targetArchive'])) {
                    return "tar archive '" . $this->details['targetArchive'] . "' not found in '" . $this->details['archive'] . "'.";
                }

                return "unable to find package file '" . $this->details['archive'] . "'";
                break;

            case self::MISSING_PACKAGE_XML:
                return "package information file '" . PackageArchive::INFO_FILE . "' not found in '" . $this->details['archive'] . "'";
                break;

            case self::INVALID_PACKAGE_NAME:
                return "'" . $this->details['packageName'] . "' is not a valid package name.";
                break;

            case self::INVALID_PACKAGE_VERSION:
                return "package version '" . $this->details['packageVersion'] . "' is invalid";
                break;

            default:
                return $this->getErrorMessage($code);
                break;
        }
    }

    /**
     * @inheritDoc
     */
    protected function logError()
    {
        // do not log errors
    }
}

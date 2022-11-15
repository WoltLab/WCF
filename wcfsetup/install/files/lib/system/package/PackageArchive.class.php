<?php

namespace wcf\system\package;

use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\io\File;
use wcf\system\io\Tar;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\XML;

/**
 * Represents the archive of a package.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package
 */
class PackageArchive
{
    /**
     * path to package archive
     * @var string
     */
    protected $archive;

    /**
     * package object of an existing package
     * @var Package
     */
    protected $package;

    /**
     * tar archive object
     * @var Tar
     */
    protected $tar;

    /**
     * general package information
     * @var array
     */
    protected $packageInfo = [];

    /**
     * author information
     * @var array
     */
    protected $authorInfo = [];

    /**
     * list of requirements
     * @var array
     */
    protected $requirements = [];

    /**
     * list of optional packages
     * @var array
     */
    protected $optionals = [];

    /**
     * list of excluded packages
     * @var array
     */
    protected $excludedPackages = [];

    /**
     * list of instructions
     * @var mixed[][]
     */
    protected $instructions = [
        'install' => [],
        'update' => [],
    ];

    /**
     * default name of the package.xml file
     * @var string
     */
    const INFO_FILE = 'package.xml';

    /**
     * marker for the void instruction
     * @var string
     */
    const VOID_MARKER = "===void===";

    /**
     * Creates a new PackageArchive object.
     *
     * @param string $archive
     * @param Package $package
     */
    public function __construct($archive, ?Package $package = null)
    {
        $this->archive = $archive;  // be careful: this is a string within this class,
        // but an object in the packageStartInstallForm.class!
        $this->package = $package;
    }

    /**
     * Returns the name of the package archive.
     *
     * @return  string
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Returns the object of the package archive.
     *
     * @return  Tar
     */
    public function getTar()
    {
        return $this->tar;
    }

    /**
     * Opens the package archive and reads package information.
     */
    public function openArchive()
    {
        // check whether archive exists and is a TAR archive
        if (!\file_exists($this->archive)) {
            throw new PackageValidationException(
                PackageValidationException::FILE_NOT_FOUND,
                ['archive' => $this->archive]
            );
        }

        // open archive and read package information
        $this->tar = new Tar($this->archive);
        $this->readPackageInfo();
    }

    /**
     * Extracts information about this package (parses package.xml).
     */
    protected function readPackageInfo()
    {
        // search package.xml in package archive
        // throw error message if not found
        if ($this->tar->getIndexByFilename(self::INFO_FILE) === false) {
            throw new PackageValidationException(
                PackageValidationException::MISSING_PACKAGE_XML,
                ['archive' => $this->archive]
            );
        }

        // extract package.xml, parse XML
        // and compile an array with XML::getElementTree()
        $xml = new XML();
        try {
            $xml->loadXML(self::INFO_FILE, $this->tar->extractToString(self::INFO_FILE));
        } catch (\Exception $e) { // bugfix to avoid file caching problems
            $xml->loadXML(self::INFO_FILE, $this->tar->extractToString(self::INFO_FILE));
        }

        // parse xml
        $xpath = $xml->xpath();
        /** @var \DOMElement $package */
        $package = $xpath->query('/ns:package')->item(0);

        // package name
        $packageName = $package->getAttribute('name');
        if (!Package::isValidPackageName($packageName)) {
            // package name is not a valid package identifier
            throw new PackageValidationException(
                PackageValidationException::INVALID_PACKAGE_NAME,
                ['packageName' => $packageName]
            );
        }

        $this->packageInfo['name'] = $packageName;

        // get package information
        $packageInformation = $xpath->query('./ns:packageinformation', $package)->item(0);
        $elements = $xpath->query('child::*', $packageInformation);
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            switch ($element->tagName) {
                case 'packagename':
                case 'packagedescription':
                case 'readme':
                case 'license':
                    // fix case-sensitive names
                    $name = $element->tagName;
                    if ($name == 'packagename') {
                        $name = 'packageName';
                    } elseif ($name == 'packagedescription') {
                        $name = 'packageDescription';
                    }

                    if (!isset($this->packageInfo[$name])) {
                        $this->packageInfo[$name] = [];
                    }

                    $languageCode = 'default';
                    if ($element->hasAttribute('language')) {
                        $languageCode = $element->getAttribute('language');
                    }

                    $this->packageInfo[$name][$languageCode] = $element->nodeValue;
                    break;

                case 'isapplication':
                    $this->packageInfo['isApplication'] = \intval($element->nodeValue);
                    break;

                case 'applicationdirectory':
                    if (\preg_match('~^[a-z0-9\-\_]+$~', $element->nodeValue)) {
                        $this->packageInfo['applicationDirectory'] = $element->nodeValue;
                    }
                    break;

                case 'packageurl':
                    $this->packageInfo['packageURL'] = $element->nodeValue;
                    break;

                case 'version':
                    if (!Package::isValidVersion($element->nodeValue)) {
                        throw new PackageValidationException(
                            PackageValidationException::INVALID_PACKAGE_VERSION,
                            ['packageVersion' => $element->nodeValue]
                        );
                    }

                    $this->packageInfo['version'] = $element->nodeValue;
                    break;

                case 'date':
                    DateUtil::validateDate($element->nodeValue);

                    $this->packageInfo['date'] = @\strtotime($element->nodeValue);
                    break;
            }
        }

        // get author information
        $authorInformation = $xpath->query('./ns:authorinformation', $package)->item(0);
        $elements = $xpath->query('child::*', $authorInformation);
        foreach ($elements as $element) {
            $tagName = ($element->tagName == 'authorurl') ? 'authorURL' : $element->tagName;
            $this->authorInfo[$tagName] = $element->nodeValue;
        }

        // get required packages
        $elements = $xpath->query('child::ns:requiredpackages/ns:requiredpackage', $package);
        foreach ($elements as $element) {
            if (!Package::isValidPackageName($element->nodeValue)) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_PACKAGE_NAME,
                    ['packageName' => $element->nodeValue]
                );
            }

            // read attributes
            $data = ['name' => $element->nodeValue];
            $attributes = $xpath->query('attribute::*', $element);
            foreach ($attributes as $attribute) {
                $data[$attribute->name] = $attribute->value;
            }

            if (
                !isset($data['minversion'])
                || !Package::isValidVersion($data['minversion'])
            ) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_REQUIRED_PACKAGE_VERSION_NUMBER,
                    [
                        'version' => $data['version'] ?? '',
                        'packageName' => $element->nodeValue,
                    ]
                );
            }

            $this->requirements[$element->nodeValue] = $data;
        }

        if (!isset($this->requirements['com.woltlab.wcf'])) {
            // Reject missing explicit com.woltlab.wcf requirement
            if ($this->packageInfo['name'] != 'com.woltlab.wcf') {
                throw new PackageValidationException(PackageValidationException::MISSING_COM_WOLTLAB_WCF_REQUIREMENT);
            }
        } else {
            // Reject com.woltlab.wcf requirements that are not reasonably recent.
            // While it might be possible for packages to be compatible with versions both before and after
            // the 5.5/6.0 jump, it is exceedingly unlikely for packages that were written for anything before 5.4
            // to still be fully compatible.
            //
            // This stops old packages that are missing both exclude and compatibility tags from being installable,
            // it also nicely excludes all versions were compatibility tags were non-deprecated (i.e. 5.2).
            if (
                !isset($this->requirements['com.woltlab.wcf']['minversion'])
                || Package::compareVersion($this->requirements['com.woltlab.wcf']['minversion'], '5.4.22', '<')
            ) {
                throw new PackageValidationException(PackageValidationException::ANCIENT_COM_WOLTLAB_WCF_REQUIREMENT);
            }
        }

        // get optional packages
        $elements = $xpath->query('child::ns:optionalpackages/ns:optionalpackage', $package);
        foreach ($elements as $element) {
            if (!Package::isValidPackageName($element->nodeValue)) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_PACKAGE_NAME,
                    ['packageName' => $element->nodeValue]
                );
            }

            // read attributes
            $data = ['name' => $element->nodeValue];
            $attributes = $xpath->query('attribute::*', $element);
            foreach ($attributes as $attribute) {
                $data[$attribute->name] = $attribute->value;
            }

            $this->optionals[] = $data;
        }

        // get excluded packages
        $elements = $xpath->query('child::ns:excludedpackages/ns:excludedpackage', $package);
        foreach ($elements as $element) {
            if (!Package::isValidPackageName($element->nodeValue)) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_PACKAGE_NAME,
                    ['packageName' => $element->nodeValue]
                );
            }

            if ($element->nodeValue === $this->packageInfo['name']) {
                throw new PackageValidationException(
                    PackageValidationException::SELF_EXCLUDE,
                    ['packageName' => $element->nodeValue]
                );
            }

            // read attributes
            $data = ['name' => $element->nodeValue];
            $attributes = $xpath->query('attribute::*', $element);
            foreach ($attributes as $attribute) {
                $data[$attribute->name] = $attribute->value;
            }

            if (
                !isset($data['version'])
                || ($data['version'] !== '*' && !Package::isValidVersion($data['version']))
            ) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_EXCLUDED_PACKAGE_VERSION_NUMBER,
                    [
                        'version' => $data['version'] ?? '',
                        'packageName' => $element->nodeValue,
                    ]
                );
            }

            $this->excludedPackages[] = $data;
        }

        // Reject packages with API compatibility information.
        $elements = $xpath->query('child::ns:compatibility/ns:api', $package);
        foreach ($elements as $element) {
            throw new PackageValidationException(PackageValidationException::INCOMPATIBLE_API_VERSION);
        }

        // get instructions
        $elements = $xpath->query('./ns:instructions', $package);
        foreach ($elements as $element) {
            $instructionData = [];
            $instructions = $xpath->query('./ns:instruction', $element);
            /** @var \DOMElement $instruction */
            foreach ($instructions as $instruction) {
                $data = [];
                $attributes = $xpath->query('attribute::*', $instruction);
                foreach ($attributes as $attribute) {
                    $data[$attribute->name] = $attribute->value;
                }

                $instructionData[] = [
                    'attributes' => $data,
                    'pip' => $instruction->getAttribute('type'),
                    'value' => $instruction->nodeValue,
                ];
            }

            $fromVersion = $element->getAttribute('fromversion');
            $type = $element->getAttribute('type');

            $void = $xpath->query('./ns:void', $element);
            if ($void->length > 1) {
                throw new PackageValidationException(PackageValidationException::VOID_NOT_ALONE);
            } elseif ($void->length == 1) {
                if (!empty($instructionData)) {
                    throw new PackageValidationException(PackageValidationException::VOID_NOT_ALONE);
                }
                if ($type == 'install') {
                    throw new PackageValidationException(PackageValidationException::VOID_ON_INSTALL);
                }

                $instructionData[] = [
                    'pip' => self::VOID_MARKER,
                    'value' => '',
                ];
            }

            if ($type == 'install') {
                $this->instructions['install'] = $instructionData;
            } else {
                $this->instructions['update'][$fromVersion] = $instructionData;
            }
        }

        // during installations, `Package::$packageVersion` can be `null` which causes issues
        // in `PackageArchive::filterUpdateInstructions()`; as update instructions are not needed
        // for installations, not filtering update instructions is okay
        if ($this->package !== null && $this->package->packageVersion !== null) {
            $this->filterUpdateInstructions();
        }

        // set default values
        if (!isset($this->packageInfo['isApplication'])) {
            $this->packageInfo['isApplication'] = 0;
        }
        if (!isset($this->packageInfo['packageURL'])) {
            $this->packageInfo['packageURL'] = '';
        }
    }

    /**
     * Filters update instructions.
     */
    protected function filterUpdateInstructions()
    {
        $validFromVersion = null;
        foreach ($this->instructions['update'] as $fromVersion => $update) {
            if (Package::checkFromversion($this->package->packageVersion, $fromVersion)) {
                $validFromVersion = $fromVersion;
                break;
            }
        }

        if ($validFromVersion === null) {
            $this->instructions['update'] = [];
        } else {
            $this->instructions['update'] = $this->instructions['update'][$validFromVersion];
        }
    }

    /**
     * Closes and deletes the tar archive of this package.
     */
    public function deleteArchive()
    {
        if ($this->tar instanceof Tar) {
            $this->tar->close();
        }

        @\unlink($this->archive);
    }

    /**
     * Checks if the new package is compatible with
     * the package that is about to be updated.
     */
    public function isValidUpdate(?Package $package = null): bool
    {
        if ($this->package === null && $package !== null) {
            $this->package = $package;

            // re-evaluate update data
            $this->filterUpdateInstructions();
        }

        // Check name of the installed package against the name of the update. Both must be identical.
        if ($this->packageInfo['name'] != $this->package->package) {
            return false;
        }

        // Check if the version number of the installed package is lower than the version number to which
        // it's about to be updated.
        if (Package::compareVersion($this->packageInfo['version'], $this->package->packageVersion) != 1) {
            return false;
        }

        // Check if the package provides an instructions block for the update from the installed package version
        if (empty($this->instructions['update'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current package is already installed, as it is not
     * possible to install non-applications multiple times within the
     * same environment.
     */
    public function isAlreadyInstalled(): bool
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_package
                WHERE   package = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->packageInfo['name']]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Returns true if the package is an application and has an unique abbreviation.
     */
    public function hasUniqueAbbreviation(): bool
    {
        if (!$this->packageInfo['isApplication']) {
            return true;
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_package
                WHERE   isApplication = ?
                    AND package LIKE ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            1,
            '%.' . Package::getAbbreviation($this->packageInfo['name']),
        ]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Returns information about the author of this package archive.
     *
     * @param string $name name of the requested information
     * @return  string|null
     */
    public function getAuthorInfo($name)
    {
        return $this->authorInfo[$name] ?? null;
    }

    /**
     * Returns information about this package.
     *
     * @param string $name name of the requested information
     * @return  mixed|null
     */
    public function getPackageInfo($name)
    {
        return $this->packageInfo[$name] ?? null;
    }

    /**
     * Returns a localized information about this package.
     *
     * @param string $name
     */
    public function getLocalizedPackageInfo($name): string
    {
        if (isset($this->packageInfo[$name][WCF::getLanguage()->getFixedLanguageCode()])) {
            return $this->packageInfo[$name][WCF::getLanguage()->getFixedLanguageCode()];
        } elseif (isset($this->packageInfo[$name]['default'])) {
            return $this->packageInfo[$name]['default'];
        }

        if (!empty($this->packageInfo[$name])) {
            return \reset($this->packageInfo[$name]);
        }

        return '';
    }

    /**
     * Returns a list of all requirements of this package.
     *
     * @return  array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Returns a list of all delivered optional packages of this package.
     *
     * @return  array
     */
    public function getOptionals()
    {
        return $this->optionals;
    }

    /**
     * Returns a list of excluded packages.
     *
     * @return  array
     */
    public function getExcludedPackages()
    {
        return $this->excludedPackages;
    }

    /**
     * Returns the package installation instructions.
     *
     * @return  array
     */
    public function getInstallInstructions()
    {
        return $this->instructions['install'];
    }

    /**
     * Returns the package update instructions.
     *
     * @return  array
     */
    public function getUpdateInstructions()
    {
        return $this->instructions['update'];
    }

    /**
     * Checks which package requirements do already exist in right version.
     * Returns a list with all existing requirements.
     *
     * @return  array
     */
    public function getAllExistingRequirements()
    {
        $existingRequirements = [];
        $existingPackages = [];
        if ($this->package !== null) {
            $sql = "SELECT      package.*
                    FROM        wcf1_package_requirement requirement
                    LEFT JOIN   wcf1_package package
                    ON          package.packageID = requirement.requirement
                    WHERE       requirement.packageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->package->packageID]);
            while ($row = $statement->fetchArray()) {
                $existingRequirements[$row['package']] = $row;
            }
        }

        // build sql
        $packageNames = [];
        $requirements = $this->getRequirements();
        foreach ($requirements as $requirement) {
            if (isset($existingRequirements[$requirement['name']])) {
                $existingPackages[$requirement['name']] = [];
                $existingPackages[$requirement['name']][$existingRequirements[$requirement['name']]['packageID']] = $existingRequirements[$requirement['name']];
            } else {
                $packageNames[] = $requirement['name'];
            }
        }

        // check whether the required packages do already exist
        if (!empty($packageNames)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package.package IN (?)", [$packageNames]);

            $sql = "SELECT  package.*
                    FROM    wcf1_package package
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                // check required package version
                if (
                    isset($requirements[$row['package']]['minversion'])
                    && Package::compareVersion(
                        $row['packageVersion'],
                        $requirements[$row['package']]['minversion']
                    ) == -1
                ) {
                    continue;
                }

                if (!isset($existingPackages[$row['package']])) {
                    $existingPackages[$row['package']] = [];
                }

                $existingPackages[$row['package']][$row['packageID']] = $row;
            }
        }

        return $existingPackages;
    }

    /**
     * Checks which package requirements do already exist in database.
     * Returns a list with the existing requirements.
     *
     * @return  array
     */
    public function getExistingRequirements()
    {
        // build sql
        $packageNames = [];
        foreach ($this->requirements as $requirement) {
            $packageNames[] = $requirement['name'];
        }

        // check whether the required packages do already exist
        $existingPackages = [];
        if (!empty($packageNames)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [$packageNames]);

            $sql = "SELECT  *
                    FROM    wcf1_package
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                if (!isset($existingPackages[$row['package']])) {
                    $existingPackages[$row['package']] = [];
                }

                $existingPackages[$row['package']][$row['packageVersion']] = $row;
            }

            // sort multiple packages by version number
            foreach ($existingPackages as $packageName => $instances) {
                \uksort($instances, [Package::class, 'compareVersion']);

                // get package with highest version number (get last package)
                $existingPackages[$packageName] = \array_pop($instances);
            }
        }

        return $existingPackages;
    }

    /**
     * Returns a list of all open requirements of this package.
     *
     * @return  array
     */
    public function getOpenRequirements()
    {
        // get all existing requirements
        $existingPackages = $this->getExistingRequirements();

        // check for open requirements
        $openRequirements = [];
        foreach ($this->requirements as $requirement) {
            if (isset($existingPackages[$requirement['name']])) {
                // package does already exist
                // maybe an update is necessary
                if (isset($requirement['minversion'])) {
                    if (
                        Package::compareVersion(
                            $existingPackages[$requirement['name']]['packageVersion'],
                            $requirement['minversion']
                        ) >= 0
                    ) {
                        // package does already exist in needed version
                        // skip installation of requirement
                        continue;
                    } else {
                        $requirement['existingVersion'] = $existingPackages[$requirement['name']]['packageVersion'];
                    }
                } else {
                    continue;
                }

                $requirement['packageID'] = $existingPackages[$requirement['name']]['packageID'];
                $requirement['action'] = 'update';
            } else {
                // package does not exist
                // new installation is necessary
                $requirement['packageID'] = 0;
                $requirement['action'] = 'install';
            }

            $openRequirements[$requirement['name']] = $requirement;
        }

        return $openRequirements;
    }

    /**
     * Extracts the requested file in the package archive to the temp folder
     * and returns the path to the extracted file.
     *
     * @param string $filename
     * @param string $tempPrefix
     * @return  string
     * @throws  PackageValidationException
     */
    public function extractTar($filename, $tempPrefix = 'package_')
    {
        // search the requested tar archive in our package archive.
        // throw error message if not found.
        if (($fileIndex = $this->tar->getIndexByFilename($filename)) === false) {
            throw new PackageValidationException(PackageValidationException::FILE_NOT_FOUND, [
                'archive' => $this->archive,
                'targetArchive' => $filename,
            ]);
        }

        // requested tar archive was found
        $fileInfo = $this->tar->getFileInfo($fileIndex);
        $filename = FileUtil::getTemporaryFilename(
            $tempPrefix,
            \preg_replace('!^.*?(\.(?:tar\.gz|tgz|tar))$!i', '\\1', $fileInfo['filename'])
        );
        $this->tar->extract($fileIndex, $filename);

        return $filename;
    }

    /**
     * Returns a list of packages which exclude this package.
     *
     * @return  Package[]
     */
    public function getConflictedExcludingPackages()
    {
        $conflictedPackages = [];
        $sql = "SELECT      package.*, package_exclusion.*
                FROM        wcf1_package_exclusion package_exclusion
                LEFT JOIN   wcf1_package package
                ON          package.packageID = package_exclusion.packageID
                WHERE       excludedPackage = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->packageInfo['name']]);
        while ($row = $statement->fetchArray()) {
            if (
                $row['excludedPackageVersion'] === '*'
                || Package::compareVersion($this->packageInfo['version'], $row['excludedPackageVersion'], '>=')
            ) {
                $conflictedPackages[$row['packageID']] = new Package(null, $row);
            }
        }

        return $conflictedPackages;
    }

    /**
     * Returns a list of packages which are excluded by this package.
     *
     * @return  Package[]
     */
    public function getConflictedExcludedPackages()
    {
        $conflictedPackages = [];
        if (!empty($this->excludedPackages)) {
            $excludedPackages = [];
            foreach ($this->excludedPackages as $excludedPackageData) {
                $excludedPackages[$excludedPackageData['name']] = $excludedPackageData['version'];
            }

            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [\array_keys($excludedPackages)]);

            $sql = "SELECT  *
                    FROM    wcf1_package
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                if (
                    $excludedPackages[$row['package']] === '*'
                    || Package::compareVersion(
                        $row['packageVersion'],
                        $excludedPackages[$row['package']],
                        '>'
                    )
                ) {
                    $row['excludedPackageVersion'] = $excludedPackages[$row['package']];
                    $conflictedPackages[$row['packageID']] = new Package(null, $row);
                }
            }
        }

        return $conflictedPackages;
    }
}

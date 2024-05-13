<?php

namespace wcf\system\package;

use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\Tar;
use wcf\system\package\validation\PackageValidationException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Represents the archive of a package.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageArchive
{
    /**
     * path to package archive
     * @var string
     */
    protected $archive;

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
     */
    public function __construct($archive)
    {
        $this->archive = $archive;  // be careful: this is a string within this class,
        // but an object in the packageStartInstallForm.class!
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
            try {
                $xml->loadXML(self::INFO_FILE, $this->tar->extractToString(self::INFO_FILE));
            } catch (SystemException $e) {
                throw new PackageValidationException(
                    PackageValidationException::INVALID_PACKAGE_XML,
                    ['libxmlOutput' => $e->getDescription()],
                );
            }
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

        $packageInformation = $xpath->query('./ns:packageinformation', $package)->item(0);
        if ($packageInformation !== null) {
            $elements = $xpath->query('child::*', $packageInformation);
            foreach ($elements as $element) {
                \assert($element instanceof \DOMElement);

                switch ($element->tagName) {
                    case 'packagename':
                    case 'packagedescription':
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

                        $this->packageInfo[$name][$languageCode] = StringUtil::trim($element->nodeValue);
                        break;

                    case 'isapplication':
                        if (isset($this->packageInfo['isApplication'])) {
                            throw new PackageValidationException(
                                PackageValidationException::DUPLICATE_PACKAGE_INFORMATION,
                                [
                                    'tag' => $element->tagName,
                                ]
                            );
                        }

                        $this->packageInfo['isApplication'] = \intval($element->nodeValue);
                        break;

                    case 'applicationdirectory':
                        if (isset($this->packageInfo['applicationdirectory'])) {
                            throw new PackageValidationException(
                                PackageValidationException::DUPLICATE_PACKAGE_INFORMATION,
                                [
                                    'tag' => $element->tagName,
                                ]
                            );
                        }

                        if (\preg_match('~^[a-z0-9\-\_]+$~', $element->nodeValue)) {
                            $this->packageInfo['applicationDirectory'] = $element->nodeValue;
                        }
                        break;

                    case 'packageurl':
                        if (isset($this->packageInfo['packageURL'])) {
                            throw new PackageValidationException(
                                PackageValidationException::DUPLICATE_PACKAGE_INFORMATION,
                                [
                                    'tag' => $element->tagName,
                                ]
                            );
                        }

                        $this->packageInfo['packageURL'] = $element->nodeValue;
                        break;

                    case 'version':
                        if (isset($this->packageInfo['version'])) {
                            throw new PackageValidationException(
                                PackageValidationException::DUPLICATE_PACKAGE_INFORMATION,
                                [
                                    'tag' => $element->tagName,
                                ]
                            );
                        }

                        if (!Package::isValidVersion($element->nodeValue)) {
                            throw new PackageValidationException(
                                PackageValidationException::INVALID_PACKAGE_VERSION,
                                ['packageVersion' => $element->nodeValue]
                            );
                        }

                        $this->packageInfo['version'] = $element->nodeValue;
                        break;

                    case 'date':
                        if (isset($this->packageInfo['date'])) {
                            throw new PackageValidationException(
                                PackageValidationException::DUPLICATE_PACKAGE_INFORMATION,
                                [
                                    'tag' => $element->tagName,
                                ]
                            );
                        }

                        DateUtil::validateDate($element->nodeValue);

                        $this->packageInfo['date'] = @\strtotime($element->nodeValue);
                        break;

                    default:
                        throw new PackageValidationException(
                            PackageValidationException::UNKNOWN_PACKAGE_INFORMATION,
                            [
                                'tag' => $element->tagName,
                            ]
                        );
                }
            }
        }

        if (!isset($this->packageInfo['version'])) {
            throw new PackageValidationException(
                PackageValidationException::MISSING_PACKAGE_VERSION
            );
        }

        if (!isset($this->packageInfo['date'])) {
            throw new PackageValidationException(
                PackageValidationException::MISSING_PACKAGE_DATE
            );
        }

        // set default values
        $this->packageInfo['isApplication'] ??= 0;
        $this->packageInfo['packageURL'] ??= '';

        // get author information
        $authorInformation = $xpath->query('./ns:authorinformation', $package)->item(0);
        if ($authorInformation !== null) {
            $elements = $xpath->query('child::*', $authorInformation);
            foreach ($elements as $element) {
                switch ($element->tagName) {
                    case 'author':
                    case 'authorurl':
                        // fix case-sensitive names
                        $name = $element->tagName;
                        if ($name == 'authorurl') {
                            $name = 'authorURL';
                        }

                        $this->authorInfo[$name] = StringUtil::trim($element->nodeValue);
                        break;
                    default:
                        throw new PackageValidationException(
                            PackageValidationException::UNKNOWN_AUTHOR_INFORMATION,
                            [
                                'tag' => $element->tagName,
                            ]
                        );
                }
            }
        }

        if (!isset($this->packageInfo['packageName'])) {
            throw new PackageValidationException(
                PackageValidationException::MISSING_DISPLAY_NAME
            );
        }

        foreach ($this->packageInfo['packageName'] as $name) {
            if ($name === '') {
                throw new PackageValidationException(
                    PackageValidationException::MISSING_DISPLAY_NAME
                );
            }
        }

        if (!isset($this->authorInfo['author']) || $this->authorInfo['author'] === '') {
            throw new PackageValidationException(
                PackageValidationException::MISSING_AUTHOR_INFORMATION
            );
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
                        'version' => $data['minversion'] ?? '',
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
     * @return  string|null
     */
    public function getAuthorInfo(string $name)
    {
        return $this->authorInfo[$name] ?? null;
    }

    /**
     * Returns information about this package.
     *
     * @return  mixed|null
     */
    public function getPackageInfo(string $name)
    {
        return $this->packageInfo[$name] ?? null;
    }

    /**
     * Returns a localized information about this package.
     */
    public function getLocalizedPackageInfo(string $name): string
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
     * @since 6.0
     */
    public function getAllUpdateInstructions()
    {
        return $this->instructions['update'];
    }

    /**
     * Returns the appropriate update instructions to update the given package version,
     * `null` if no appropriate instruction could be found.
     *
     * @since 6.0
     */
    public function getUpdateInstructionsFor(string $version): ?array
    {
        foreach ($this->instructions['update'] as $fromVersion => $instructions) {
            if (Package::checkFromversion($version, $fromVersion)) {
                return $instructions;
            }
        }

        return null;
    }

    /**
     * Checks which package requirements do already exist in database.
     * Returns a list with the existing requirements.
     *
     * @return  array
     */
    public function getExistingRequirements()
    {
        if ($this->packageInfo['name'] === 'com.woltlab.wcf') {
            return [];
        }

        $packageNames = \array_column($this->requirements, 'name');
        \assert($packageNames !== []);

        // check whether the required packages do already exist
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$packageNames]);

        $sql = "SELECT  *
                FROM    wcf1_package
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $existingPackages = [];
        while ($row = $statement->fetchArray()) {
            $existingPackages[$row['package']] = $row;
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
                'targetArchive' => $this->archive,
                'archive' => $filename,
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

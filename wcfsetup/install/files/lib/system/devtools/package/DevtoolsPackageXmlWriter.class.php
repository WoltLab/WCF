<?php

namespace wcf\system\devtools\package;

use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\language\LanguageFactory;
use wcf\util\XMLWriter;

/**
 * Writes the `package.xml` file of a project.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class DevtoolsPackageXmlWriter
{
    /**
     * data used to write the `package.xml` file
     * @var array
     */
    protected $packageXmlData;

    /**
     * devtools project whose `package.xml` file will be written
     * @var DevtoolsProject
     */
    protected $project;

    /**
     * xml writer object
     * @var XMLWriter
     */
    protected $xmlWriter;

    /**
     * Creates a new `DevtoolsPackageXmlWriter` object.
     *
     * @param DevtoolsProject $project
     * @param array $packageXmlData
     */
    public function __construct(DevtoolsProject $project, array $packageXmlData)
    {
        $this->project = $project;
        $this->packageXmlData = $packageXmlData;
    }

    /**
     * Returns `true` if the given string needs to be placed in a CDATA
     * section or `false`, otherwise.
     *
     * @param string $string
     * @return  bool
     */
    protected function requiresCdata($string)
    {
        return \strpos($string, '<') !== false
            || \strpos($string, '>') !== false
            || \strpos($string, '&') !== false;
    }

    /**
     * Writes the `package.xml` file.
     */
    public function write()
    {
        $this->xmlWriter = new XMLWriter();
        $this->xmlWriter->beginDocument(
            'package',
            'http://www.woltlab.com',
            'http://www.woltlab.com/XSD/6.0/package.xsd',
            ['name' => $this->packageXmlData['packageIdentifier']]
        );

        $this->writePackageInformation();
        $this->writeAuthorInformation();
        $this->writeRequiredPackages();
        $this->writeOptionalPackages();
        $this->writeExcludedPackages();
        $this->writeInstructions();

        $this->xmlWriter->endDocument($this->project->getPackageXmlPath());
    }

    /**
     * Writes the `authorinformation` element.
     */
    protected function writeAuthorInformation()
    {
        $this->xmlWriter->startElement('authorinformation');

        $this->xmlWriter->writeElement(
            'author',
            $this->packageXmlData['author'],
            [],
            $this->requiresCdata($this->packageXmlData['author'])
        );
        if (isset($this->packageXmlData['authorUrl']) && $this->packageXmlData['authorUrl'] !== '') {
            $this->xmlWriter->writeElement(
                'authorurl',
                $this->packageXmlData['authorUrl'],
                [],
                $this->requiresCdata($this->packageXmlData['authorUrl'])
            );
        }

        $this->xmlWriter->endElement();
    }

    /**
     * Writes the `optionalpackages` element.
     */
    protected function writeExcludedPackages()
    {
        if (!empty($this->packageXmlData['excludedPackages'])) {
            $this->xmlWriter->startElement('excludedpackages');

            foreach ($this->packageXmlData['excludedPackages'] as $excludedPackage) {
                $attributes = [];
                if (!empty($excludedPackage['version'])) {
                    $attributes['version'] = $excludedPackage['version'];
                }

                $this->xmlWriter->writeElement(
                    'excludedpackage',
                    $excludedPackage['packageIdentifier'],
                    $attributes,
                    false
                );
            }

            $this->xmlWriter->endElement();
        }
    }

    /**
     * Writes the `instructions` elements.
     */
    protected function writeInstructions()
    {
        if (empty($this->packageXmlData['instructions'])) {
            return;
        }

        foreach ($this->packageXmlData['instructions'] as $instructions) {
            $attributes = ['type' => $instructions['type']];
            if ($instructions['type'] === 'update') {
                $attributes['fromversion'] = $instructions['fromVersion'];
            }

            $this->xmlWriter->startElement('instructions', $attributes);

            foreach ($instructions['instructions'] as $instruction) {
                $attributes = ['type' => $instruction['pip']];
                if (isset($instruction['runStandalone']) && $instruction['runStandalone'] !== "0") {
                    $attributes['run'] = 'standalone';
                }
                if (!empty($instruction['application'])) {
                    $attributes['application'] = $instruction['application'];
                }

                $this->xmlWriter->writeElement('instruction', $instruction['value'], $attributes, false);
            }

            $this->xmlWriter->endElement();
        }
    }

    /**
     * Writes the `optionalpackages` element.
     */
    protected function writeOptionalPackages()
    {
        if (!empty($this->packageXmlData['optionalPackages'])) {
            $this->xmlWriter->startElement('optionalpackages');

            foreach ($this->packageXmlData['optionalPackages'] as $optionalPackage) {
                $this->xmlWriter->writeElement(
                    'optionalpackage',
                    $optionalPackage['packageIdentifier'],
                    ['file' => "optionals/{$optionalPackage['packageIdentifier']}.tar"],
                    false
                );
            }

            $this->xmlWriter->endElement();
        }
    }

    /**
     * Writes a child of the `packageinformation` element with i18n data.
     *
     * @param string $information
     * @param null|string $elementName is set to lowercase version of `$information` if missing
     */
    protected function writeI18nPackageInformation($information, $elementName = null)
    {
        if ($elementName === null) {
            $elementName = \strtolower($information);
        }

        $english = LanguageFactory::getInstance()->getLanguageByCode('en');

        if (isset($this->packageXmlData[$information . '_i18n'])) {
            $defaultLanguageID = null;
            if ($english !== null && isset($this->packageXmlData[$information . '_i18n'][$english->languageID])) {
                $defaultLanguageID = $english->languageID;
            } else {
                \reset($this->packageXmlData[$information . '_i18n']);
                $defaultLanguageID = \key($this->packageXmlData[$information . '_i18n']);
            }

            $this->xmlWriter->writeElement(
                $elementName,
                $this->packageXmlData[$information . '_i18n'][$defaultLanguageID],
                [],
                $this->requiresCdata($this->packageXmlData[$information . '_i18n'][$defaultLanguageID])
            );

            foreach ($this->packageXmlData[$information . '_i18n'] as $languageID => $informationValue) {
                if ($languageID !== $defaultLanguageID && $informationValue !== "") {
                    $this->xmlWriter->writeElement(
                        $elementName,
                        $informationValue,
                        ['language' => LanguageFactory::getInstance()->getLanguage($languageID)->languageCode],
                        $this->requiresCdata($informationValue)
                    );
                }
            }
        } elseif (
            isset($this->packageXmlData[$information])
            && $this->packageXmlData[$information] !== ""
        ) {
            $this->xmlWriter->writeElement(
                $elementName,
                $this->packageXmlData[$information],
                [],
                $this->requiresCdata($this->packageXmlData[$information])
            );
        }
    }

    /**
     * Writes the `packageinformation` element.
     */
    protected function writePackageInformation()
    {
        $this->xmlWriter->startElement('packageinformation');

        $this->xmlWriter->writeComment(" {$this->packageXmlData['packageIdentifier']} ");

        $this->writeI18nPackageInformation('packageName');
        $this->writeI18nPackageInformation('packageDescription');

        if (!empty($this->packageXmlData['isApplication'])) {
            $this->xmlWriter->writeElement(
                'isapplication',
                \intval($this->packageXmlData['isApplication']),
                [],
                false
            );
        }
        if (!empty($this->packageXmlData['applicationDirectory'])) {
            $this->xmlWriter->writeElement(
                'applicationdirectory',
                $this->packageXmlData['applicationDirectory'],
                [],
                false
            );
        }
        $this->xmlWriter->writeElement(
            'version',
            $this->packageXmlData['version'],
            [],
            false
        );
        $this->xmlWriter->writeElement(
            'date',
            $this->packageXmlData['date'],
            [],
            false
        );
        if (!empty($this->packageXmlData['packageUrl'])) {
            $this->xmlWriter->writeElement(
                'packageurl',
                $this->packageXmlData['packageUrl'],
                [],
                $this->requiresCdata($this->packageXmlData['packageUrl'])
            );
        }
        $this->writeI18nPackageInformation('license');

        $this->xmlWriter->endElement();
    }

    /**
     * Writes the `optionalpackages` element.
     */
    protected function writeRequiredPackages()
    {
        if (!empty($this->packageXmlData['requiredPackages'])) {
            $this->xmlWriter->startElement('requiredpackages');

            foreach ($this->packageXmlData['requiredPackages'] as $requiredPackage) {
                $attributes = [];
                if (!empty($requiredPackage['minVersion'])) {
                    $attributes['minversion'] = $requiredPackage['minVersion'];
                }
                if (!empty($requiredPackage['file'])) {
                    $attributes['file'] = "requirements/{$requiredPackage['packageIdentifier']}.tar";
                }

                $this->xmlWriter->writeElement(
                    'requiredpackage',
                    $requiredPackage['packageIdentifier'],
                    $attributes,
                    false
                );
            }

            $this->xmlWriter->endElement();
        }
    }
}

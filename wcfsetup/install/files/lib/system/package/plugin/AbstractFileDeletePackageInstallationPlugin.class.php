<?php

namespace wcf\system\package\plugin;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\XML;

/**
 * Abstract implementation of a package installation plugin deleting a certain type of files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
abstract class AbstractFileDeletePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * Returns the name of the database table that logs the installed files.
     */
    abstract protected function getLogTableName(): string;

    /**
     * Returns the name of the column in the log table returned by `getLogTableName()` that contains
     * the names of the relevant files.
     */
    abstract protected function getFilenameTableColumn(): string;

    protected function getPipName(): string
    {
        return $this->getXsdFilename();
    }

    /**
     * Returns the actual absolute path of the given file.
     */
    protected function getFilePath(string $filename, string $application): string
    {
        return Application::getDirectory($application) . $filename;
    }

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $groupedFiles = [];
        foreach ($items as $item) {
            $file = $item['value'];
            $application = 'wcf';
            if (!empty($item['attributes']['application'])) {
                $application = $item['attributes']['application'];
            } elseif ($this->installation->getPackage()->isApplication) {
                $application = Package::getAbbreviation($this->installation->getPackage()->package);
            }

            if (!isset($groupedFiles[$application])) {
                $groupedFiles[$application] = [];
            }
            $groupedFiles[$application][] = $file;
        }

        $logFiles = [];
        foreach ($groupedFiles as $application => $files) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("{$this->getFilenameTableColumn()} IN (?)", [$files]);
            $conditions->add('application = ?', [$application]);

            $sql = "SELECT  packageID, application, {$this->getFilenameTableColumn()}
                    FROM    {$this->getLogTableName()}
                    {$conditions}";
            $searchStatement = WCF::getDB()->prepare($sql);
            $searchStatement->execute($conditions->getParameters());

            while ($row = $searchStatement->fetchArray()) {
                if (!isset($logFiles[$row['application']])) {
                    $logFiles[$row['application']] = [];
                }
                $logFiles[$row['application']][$row[$this->getFilenameTableColumn()]] = $row['packageID'];
            }
        }

        foreach ($groupedFiles as $application => $files) {
            foreach ($files as $file) {
                $filePackageID = $logFiles[$application][$file] ?? null;
                if ($filePackageID !== null && $filePackageID != $this->installation->getPackageID()) {
                    continue;
                }

                $filePath = $this->getFilePath($file, $application);

                $this->safeDeleteFile($filePath);
            }
        }

        WCF::getDB()->beginTransaction();
        foreach ($logFiles as $application => $files) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("{$this->getFilenameTableColumn()} IN (?)", [\array_keys($files)]);
            $conditions->add('application = ?', [$application]);
            $conditions->add('packageID = ?', [$this->installation->getPackageID()]);

            $sql = "DELETE FROM {$this->getLogTableName()}
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
        }
        WCF::getDB()->commitTransaction();
    }

    private static function isFilesystemCaseSensitive(): bool
    {
        static $isFilesystemCaseSensitive = null;

        if ($isFilesystemCaseSensitive === null) {
            $testFilePath = __FILE__;

            $invertedCase = \sprintf(
                '%s/%s',
                \dirname($testFilePath),
                \strtr(
                    \basename($testFilePath),
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",
                    "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
                )
            );

            $isFilesystemCaseSensitive = !\file_exists($invertedCase);
        }

        return $isFilesystemCaseSensitive;
    }

    private function safeDeleteFile(string $filePath): void
    {
        if (!\file_exists($filePath)) {
            return;
        }

        if (self::isFilesystemCaseSensitive()) {
            \unlink($filePath);

            return;
        }

        // If the filesystem is case insensitive, we must check, whether the casing of the file
        // matches the casing of the file, which we want to delete. Therefore, we must iterate
        // through the whole dir to find the potential file.
        $pathInfo = \pathinfo($filePath);
        foreach (\glob($pathInfo['dirname'] . '/*') as $file) {
            if (\basename($file) === $pathInfo['basename']) {
                \unlink($filePath);
                break;
            }
        }
    }

    /**
     * @inheritDoc
     */
    final protected function import(array $row, array $data)
    {
        // Does nothing, imports are not supported.
    }

    /**
     * @inheritDoc
     */
    final protected function prepareImport(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    final protected function findExistingItem(array $data)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function hasUninstall()
    {
        // File deletions cannot be reverted.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // File deletions cannot be reverted.
    }

    /**
     * Returns the language item with the description of the file field or `null` if no description
     * should be shown.
     */
    protected function getFileFieldDescription(): ?string
    {
        $languageItem = "wcf.acp.pip.{$this->getPipName()}.{$this->tagName}.description";

        return WCF::getLanguage()->get($languageItem, true) ?: null;
    }

    /**
     * @inheritDoc
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create($this->tagName)
                ->label("wcf.acp.pip.{$this->getPipName()}.{$this->tagName}")
                ->description($this->getFileFieldDescription())
                ->required(),
            SingleSelectionFormField::create('application')
                ->label("wcf.acp.pip.{$this->getPipName()}.application")
                ->options(static function (): array {
                    $options = [
                        '' => 'wcf.global.noSelection',
                    ];

                    $apps = ApplicationHandler::getInstance()->getApplications();
                    \usort($apps, static function (Application $a, Application $b) {
                        return $a->getPackage()->getTitle() <=> $b->getPackage()->getTitle();
                    });

                    foreach ($apps as $application) {
                        $options[$application->getAbbreviation()] = $application->getPackage()->getTitle();
                    }

                    return $options;
                })
                ->nullable(),
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        return [
            'application' => $element->getAttribute('application') ?? 'wcf',
            $this->tagName => $element->nodeValue,
            'packageID' => $this->installation->getPackage()->packageID,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        $app = $element->getAttribute('application') ?? 'wcf';

        return \sha1($app . '_' . $element->nodeValue);
    }

    /**
     * @inheritDoc
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            $this->tagName => "wcf.acp.pip.{$this->getPipName()}.{$this->tagName}",
            'application' => "wcf.acp.pip.{$this->getPipName()}.application",
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function insertNewXmlElement(XML $xml, \DOMElement $newElement)
    {
        $delete = $xml->xpath()->query('/ns:data/ns:delete')->item(0);
        if ($delete === null) {
            $data = $xml->xpath()->query('/ns:data')->item(0);
            $delete = $xml->getDocument()->createElement('delete');
            DOMUtil::prepend($delete, $data);
        }

        $delete->appendChild($newElement);
    }

    /**
     * @inheritDoc
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $file = $document->createElement($this->tagName);

        $data = $form->getData()['data'];
        if (!empty($data['application'])) {
            $file->setAttribute('application', $data['application']);
        }
        $file->nodeValue = $data[$this->tagName];

        return $file;
    }

    /**
     * @inheritDoc
     */
    final protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function saveObject(\DOMElement $newElement, ?\DOMElement $oldElement = null)
    {
        $newElementData = $this->getElementData($newElement, true);

        $this->handleDelete([[
            'attributes' => [
                'application' => $newElementData['application'],
            ],
            'value' => $newElementData[$this->tagName],
        ]]);
    }

    /**
     * @inheritDoc
     */
    final protected function deleteObject(\DOMElement $element)
    {
        // Reverting file deletions is not supported. Use the `file` PIP instead.
    }

    /**
     * @inheritDoc
     */
    protected function getImportElements(\DOMXPath $xpath)
    {
        return $xpath->query('/ns:data/ns:delete/ns:' . $this->tagName);
    }

    /**
     * @inheritDoc
     */
    protected function getEmptyXml()
    {
        $xsdFilename = $this->getXsdFilename();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/6.0/{$xsdFilename}.xsd">
	<delete></delete>
</data>
XML;
    }
}

<?php

namespace wcf\system\package\plugin;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
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
 * Files files installed with the `file` package installation plugin.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
 * @since   5.5
 */
class FileDeletePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $tagName = 'file';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "SELECT  packageID
                FROM    wcf1_package_installation_file_log
                WHERE   filename = ?
                    AND application = ?
                    AND packageID = ?";
        $searchStatement = WCF::getDB()->prepare($sql);

        $sql = "DELETE FROM wcf1_package_installation_file_log
                WHERE       packageID = ?
                        AND filename = ?";
        $deleteStatement = WCF::getDB()->prepare($sql);

        foreach ($items as $item) {
            $file = $item['value'];
            $application = 'wcf';
            if (!empty($item['attributes']['application'])) {
                $application = $item['attributes']['application'];
            } elseif ($this->installation->getPackage()->isApplication) {
                $application = Package::getAbbreviation($this->installation->getPackage()->package);
            }

            $searchStatement->execute([
                $file,
                $application,
                $this->installation->getPackageID(),
            ]);

            $filePackageID = $searchStatement->fetchSingleColumn();
            if ($filePackageID !== false && $filePackageID != $this->installation->getPackageID()) {
                throw new \UnexpectedValueException(
                    "File '{$file}' does not belong to package '{$this->installation->getPackage()->package}'
                    but to package '" . PackageCache::getInstance()->getPackage($filePackageID)->package . "'."
                );
            }

            $filePath = Application::getDirectory($application) . $file;
            if (\file_exists($filePath)) {
                \unlink($filePath);
            }

            $deleteStatement->execute([
                $this->installation->getPackageID(),
                $file,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function import(array $row, array $data)
    {
        throw new \LogicException("The `fileDelete` package installation plugin does not support imports.");
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
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
    protected function addFormFields(IFormDocument $form)
    {
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('file')
                ->label('wcf.acp.pip.fileDelete.file')
                ->required(),
            SingleSelectionFormField::create('application')
                ->label('wcf.acp.pip.fileDelete.application')
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
            'file' => $element->nodeValue,
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
            'file' => 'wcf.acp.pip.fileDelete.file',
            'application' => 'wcf.acp.pip.fileDelete.application',
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
        $file->nodeValue = $data['file'];

        return $file;
    }

    /**
     * @inheritDoc
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
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
            'value' => $newElementData['file'],
        ]]);
    }

    /**
     * @inheritDoc
     */
    protected function deleteObject(\DOMElement $element)
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
        $apiVersion = WSC_API_VERSION;

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/{$apiVersion}/{$xsdFilename}.xsd">
	<delete></delete>
</data>
XML;
    }
}

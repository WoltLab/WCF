<?php

namespace wcf\system\devtools\pip;

use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\IEditableCachedObject;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Provides default implementations of the methods of the
 *  `wcf\system\devtools\pip\IGuiPackageInstallationPlugin`
 * interface for an xml-based package installation plugin.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @property    PackageInstallationDispatcher|DevtoolsPackageInstallationDispatcher $installation
 * @mixin   AbstractXMLPackageInstallationPlugin
 * @mixin   IGuiPackageInstallationPlugin
 */
trait TXmlGuiPackageInstallationPlugin
{
    /**
     * dom element representing the original data of the edited entry
     * @var null|\DOMElement
     */
    protected $editedEntry;

    /**
     * type of the currently handled pip entries
     * @var null|string
     */
    protected $entryType;

    /**
     * Adds a delete element to the xml file based on the given installation
     * element.
     *
     * @param \DOMElement $element installation element
     */
    protected function addDeleteElement(\DOMElement $element)
    {
        $document = $element->ownerDocument;

        $delete = $document->documentElement->getElementsByTagName('delete')->item(0);

        if ($delete === null) {
            $delete = $document->createElement('delete');
            $document->documentElement->appendChild($delete);
        }

        $delete->appendChild($document->importNode($this->prepareDeleteXmlElement($element)));
    }

    /**
     * Adds a new entry of this pip based on the data provided by the given
     * form.
     *
     * @param IFormDocument $form
     */
    public function addEntry(IFormDocument $form)
    {
        $xml = $this->getProjectXml();
        $document = $xml->getDocument();

        $newElement = $this->createXmlElement($document, $form);
        $this->insertNewXmlElement($xml, $newElement);

        $this->saveObject($newElement);

        $xml->write($this->getXmlFileLocation());
    }

    /**
     * Adds all fields to the given form to add or edit an entry.
     *
     * @param IFormDocument $form
     */
    abstract protected function addFormFields(IFormDocument $form);

    /**
     * Adds optional child elements to the given elements based on the given
     * child data and form.
     *
     * @param \DOMElement $element element to which the child elements are added
     * @param array $children
     * @param IFormDocument $form form containing the children's data
     */
    protected function appendElementChildren(\DOMElement $element, array $children, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $document = $element->ownerDocument;

        foreach ($children as $key => $value) {
            if (\is_string($key)) {
                $childName = $key;
                if (!\is_array($value)) {
                    $isOptional = true;
                    $cdata = false;
                    $defaultValue = $value;
                } else {
                    $isOptional = \array_key_exists('defaultValue', $value);
                    $cdata = $value['cdata'] ?? false;
                    $defaultValue = $value['defaultValue'] ?? null;
                }
            } else {
                $childName = $value;
                $isOptional = false;
                $cdata = false;
                $defaultValue = null;
            }

            if (!$isOptional || (isset($data[$childName]) && $data[$childName] !== $defaultValue)) {
                if ($cdata) {
                    $childElement = $document->createElement($childName);
                    $childElement->appendChild($document->createCDATASection($data[$childName]));
                } else {
                    $childElement = $document->createElement(
                        $childName,
                        (string)$data[$childName]
                    );
                }

                $element->appendChild($childElement);
            }
        }
    }

    /**
     * Creates a new XML element for the given document using the data provided
     * by the given form and return the new dom element.
     *
     * @param \DOMDocument $document
     * @param IFormDocument $form
     * @return  \DOMElement
     */
    abstract protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form);

    /**
     * Creates a new XML element for the given document using the data provided
     * by the given form and return the new dom element.
     *
     * This method internally calls `prepareXmlElement()` and fires an event.
     *
     * @param \DOMDocument $document
     * @param IFormDocument $form
     * @return  \DOMElement
     */
    protected function createXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $xmlElement = $this->prepareXmlElement($document, $form);

        $data = [
            'document' => $document,
            'element' => $xmlElement,
            'form' => $form,
        ];

        EventHandler::getInstance()->fireAction($this, 'didPrepareXmlElement', $data);

        if (!($data['element'] instanceof \DOMElement)) {
            throw new \UnexpectedValueException('XML element is no "\DOMElement" object anymore.');
        }

        return $data['element'];
    }

    /**
     * Deletes the entry of this pip with the given identifier and, based
     * on the value of `$addDeleteInstruction`, adds a delete instruction.
     *
     * @param string $identifier
     * @param bool $addDeleteInstruction
     */
    public function deleteEntry($identifier, $addDeleteInstruction)
    {
        $xml = $this->getProjectXml();

        $element = $this->getElementByIdentifier($xml, $identifier);

        if ($element === null) {
            throw new \InvalidArgumentException("Unknown entry with identifier '{$identifier}'.");
        }

        if (!$this->supportsDeleteInstruction() && $addDeleteInstruction) {
            throw new \InvalidArgumentException(
                "This package installation plugin does not support delete instructions."
            );
        }

        $this->deleteObject($element);

        if ($addDeleteInstruction) {
            $this->addDeleteElement($element);
        }

        $document = $element->ownerDocument;

        DOMUtil::removeNode($element);

        $deleteFile = $this->sanitizeXmlFileAfterDeleteEntry($document);

        if ($deleteFile) {
            \unlink($this->getXmlFileLocation());
        } else {
            $xml->write($this->getXmlFileLocation());
        }

        if (\is_subclass_of($this->className, IEditableCachedObject::class)) {
            \call_user_func([$this->className, 'resetCache']);
        }
    }

    /**
     * Sanitizes the given document after an entry has been deleted by removing
     * empty parent elements and returns `true` if the xml file should be deleted
     * because there is no content left.
     *
     * @param \DOMDocument $document sanitized document
     * @return  bool
     */
    protected function sanitizeXmlFileAfterDeleteEntry(\DOMDocument $document)
    {
        $data = $document->getElementsByTagName('data')->item(0);
        $import = $data->getElementsByTagName('import')->item(0);

        // remove empty import node
        if ($import->childNodes->length === 0) {
            DOMUtil::removeNode($import);

            // delete file if empty
            if ($data->childNodes->length === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes the given element from database.
     *
     * @param \DOMElement $element
     */
    protected function deleteObject(\DOMElement $element)
    {
        $name = $element->getAttribute('name');
        if ($name !== '') {
            $this->handleDelete([['attributes' => ['name' => $name]]]);
        } else {
            $identifier = $element->getAttribute('identifier');
            if ($identifier !== '') {
                $this->handleDelete([['attributes' => ['identifier' => $identifier]]]);
            } else {
                throw new \LogicException("Cannot delete object using the default implementations.");
            }
        }
    }

    /**
     * Edits the entry of this pip with the given identifier based on the data
     * provided by the given form and returns the new identifier of the entry
     * (or the old identifier if it has not changed).
     *
     * @param IFormDocument $form
     * @param string $identifier
     * @return  string          new identifier
     */
    public function editEntry(IFormDocument $form, $identifier)
    {
        $xml = $this->getProjectXml();
        $document = $xml->getDocument();

        // add updated element
        $newElement = $this->createXmlElement($document, $form);

        // replace old element
        $element = $this->getElementByIdentifier($xml, $identifier);
        DOMUtil::replaceElement($element, $newElement, false);

        $this->saveObject($newElement, $element);

        $xml->write($this->getXmlFileLocation());

        return $this->getElementIdentifier($newElement);
    }

    /**
     * Returns additional template code for the form to add and edit entries.
     *
     * @return  string
     */
    public function getAdditionalTemplateCode()
    {
        return '';
    }

    /**
     * Checks if the given string needs to be encapsuled by cdata and does so
     * if required.
     *
     * @param string $value
     * @return  string
     */
    protected function getAutoCdataValue($value)
    {
        if (\strpos('<', $value) !== false || \strpos('>', $value) !== false || \strpos('&', $value) !== false) {
            $value = '<![CDATA[' . StringUtil::escapeCDATA($value) . ']]>';
        }

        return $value;
    }

    /**
     * Returns the `import` element with the given identifier.
     *
     * @param XML $xml
     * @param string $identifier
     * @return  \DOMElement|null
     */
    protected function getElementByIdentifier(XML $xml, $identifier)
    {
        foreach ($this->getImportElements($xml->xpath()) as $element) {
            if ($this->getElementIdentifier($element) === $identifier) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Extracts the PIP object data from the given XML element.
     *
     * @param \DOMElement $element element whose data is returned
     * @param bool $saveData is `true` if data is intended to be saved and otherwise `false`
     * @return  array
     */
    abstract protected function fetchElementData(\DOMElement $element, $saveData);

    /**
     * Extracts the PIP object data from the given XML element by calling
     * `fetchElementData` and firing an event.
     *
     * @param \DOMElement $element element whose data is returned
     * @param bool $saveData is `true` if data is intended to be saved and otherwise `false`
     * @return  array
     */
    protected function getElementData(\DOMElement $element, $saveData = false)
    {
        $elementData = $this->fetchElementData($element, $saveData);

        $data = [
            'element' => $element,
            'elementData' => $elementData,
            'saveData' => $saveData,
        ];

        EventHandler::getInstance()->fireAction($this, 'getElementData', $data);

        if (!\is_array($data['elementData'])) {
            throw new \UnexpectedValueException("Element data is no longer an array.");
        }

        return $data['elementData'];
    }

    /**
     * Returns the identifier of the given `import` element.
     *
     * @param \DOMElement $element
     * @return  string
     */
    abstract protected function getElementIdentifier(\DOMElement $element);

    /**
     * Returns the xml code of an empty xml file with the appropriate structure
     * present for a new entry to be added as if it was added to an existing
     * file.
     *
     * @return  string
     */
    protected function getEmptyXml()
    {
        $xsdFilename = $this->getXsdFilename();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/6.0/{$xsdFilename}.xsd">
	<import></import>
</data>
XML;
    }

    /**
     * Returns the name of the xsd file for this package installation plugin
     * (without the file extension).
     *
     * @return  string
     */
    protected function getXsdFilename()
    {
        $classNamePieces = \explode('\\', static::class);

        return \lcfirst(\str_replace('PackageInstallationPlugin', '', \array_pop($classNamePieces)));
    }

    /**
     * Returns a list of all pip entries of this pip.
     *
     * @return  IDevtoolsPipEntryList
     */
    public function getEntryList()
    {
        $xml = $this->getProjectXml();
        $xpath = $xml->xpath();

        $entryList = new DevtoolsPipEntryList();
        $this->setEntryListKeys($entryList);

        /** @var \DOMElement $element */
        foreach ($this->getImportElements($xpath) as $element) {
            $entryList->addEntry(
                $this->getElementIdentifier($element),
                // we skip the event here to avoid firing all of those events
                \array_intersect_key($this->fetchElementData($element, false), $entryList->getKeys())
            );
        }

        return $entryList;
    }

    /**
     * Returns the list of available entry types. If only one entry type is
     * available, this method returns an empty array.
     *
     * For package installation plugins that support entries and categories
     * for these entries, `['entries', 'categories']` should be returned.
     *
     * @return  string[]
     */
    public function getEntryTypes()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getImportElements(\DOMXPath $xpath)
    {
        if ($this->entryType !== null) {
            if (\substr($this->entryType, -3) === 'ies') {
                $objectTag = \substr($this->entryType, 0, -3) . 'y';
            } else {
                $objectTag = \substr($this->entryType, 0, -1);
            }

            return $xpath->query('/ns:data/ns:import/ns:' . $this->entryType . '/ns:' . $objectTag);
        }

        return parent::getImportElements($xpath);
    }

    /**
     * Returns the xml object for this pip.
     *
     * @return  XML
     */
    protected function getProjectXml()
    {
        $fileLocation = $this->getXmlFileLocation();

        $xml = new XML();
        if (!\file_exists($fileLocation)) {
            $xml->loadXML($fileLocation, $this->getEmptyXml());
        } else {
            $xml->load($fileLocation);
        }

        return $xml;
    }

    /**
     * Returns the location of the xml file for this pip.
     *
     * @return  string
     */
    protected function getXmlFileLocation()
    {
        /** @var DevtoolsProject $project */
        $project = $this->installation->getProject();

        return $project->path . ($project->getPackage()->package === 'com.woltlab.wcf' ? 'com.woltlab.wcf/' : '') . static::getDefaultFilename();
    }

    /**
     * Inserts the give new element into the given XML document.
     *
     * @param XML $xml XML document to which the element is added
     * @param \DOMElement $newElement added new element
     */
    protected function insertNewXmlElement(XML $xml, \DOMElement $newElement)
    {
        $import = $xml->xpath()->query('/ns:data/ns:import')->item(0);
        if ($import === null) {
            $data = $xml->xpath()->query('/ns:data')->item(0);
            $import = $xml->getDocument()->createElement('import');
            DOMUtil::prepend($import, $data);
        }

        $import->appendChild($newElement);
    }

    /**
     * Populates the given form to be used for adding and editing entries
     * managed by this PIP.
     *
     * @param IFormDocument $form
     */
    public function populateForm(IFormDocument $form)
    {
        $eventParameters = ['form' => $form];

        EventHandler::getInstance()->fireAction($this, 'beforeAddFormFields', $eventParameters);

        if (!($eventParameters['form'] instanceof IFormDocument)) {
            throw new \UnexpectedValueException('Form document is no longer a "' . IFormDocument::class . '" object.');
        }

        $this->addFormFields($eventParameters['form']);

        if (!($eventParameters['form'] instanceof IFormDocument)) {
            throw new \UnexpectedValueException('Form document is no longer a "' . IFormDocument::class . '" object.');
        }

        EventHandler::getInstance()->fireAction($this, 'afterAddFormFields', $eventParameters);
    }

    /**
     * Returns a delete xml element based on the given import element.
     *
     * @param \DOMElement $element
     * @return  \DOMElement
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        if (!$this->supportsDeleteInstruction()) {
            throw new \BadMethodCallException(
                "Cannot prepare delete xml element if delete instructions are not supported."
            );
        }

        $name = $element->getAttribute('name');
        if ($name !== '') {
            $element = $element->ownerDocument->createElement($this->tagName);
            $element->setAttribute('name', $name);
        } else {
            $identifier = $element->getAttribute('identifier');
            if ($identifier !== '') {
                $element = $element->ownerDocument->createElement($this->tagName);
                $element->setAttribute('identifier', $identifier);
            } else {
                throw new \LogicException("Cannot prepare delete xml element using the default implementations.");
            }
        }

        return $element;
    }

    /**
     * Saves an object represented by an XML element in the database by either
     * creating a new element (if `$oldElement = null`) or updating an existing
     * element.
     *
     * @param \DOMElement $newElement XML element with new data
     * @param \DOMElement|null $oldElement XML element with old data
     */
    protected function saveObject(\DOMElement $newElement, ?\DOMElement $oldElement = null)
    {
        $newElementData = $this->getElementData($newElement, true);

        $existingRow = [];
        if ($oldElement !== null) {
            $sqlData = $this->findExistingItem($this->getElementData($oldElement, true));

            if ($sqlData !== null) {
                $statement = WCF::getDB()->prepareStatement($sqlData['sql']);
                $statement->execute($sqlData['parameters']);

                $existingRow = $statement->fetchArray() ?: [];
            }
        }

        $this->import($existingRow, $newElementData);

        $eventParameters = [
            'newElement' => $newElement,
            'oldElement' => $oldElement,
        ];
        EventHandler::getInstance()->fireAction($this, 'saveObject', $eventParameters);

        $this->postImport();

        if (\is_subclass_of($this->className, IEditableCachedObject::class)) {
            \call_user_func([$this->className, 'resetCache']);
        }
    }

    /**
     * Informs the pip of the identifier of the edited entry if the form to
     * edit that entry has been submitted.
     *
     * @param string $identifier
     *
     * @throws  \InvalidArgumentException   if no such entry exists
     */
    public function setEditedEntryIdentifier($identifier)
    {
        $this->editedEntry = $this->getElementByIdentifier($this->getProjectXml(), $identifier);

        if ($this->editedEntry === null) {
            throw new \InvalidArgumentException("Unknown entry with identifier '{$identifier}'.");
        }
    }

    /**
     * Adds the data of the pip entry with the given identifier into the
     * given form and returns `true`. If no entry with the given identifier
     * exists, `false` is returned.
     *
     * @param string $identifier
     * @param IFormDocument $document
     * @return  bool
     */
    public function setEntryData($identifier, IFormDocument $document)
    {
        $xml = $this->getProjectXml();

        $element = $this->getElementByIdentifier($xml, $identifier);
        if ($element === null) {
            return false;
        }

        $data = $this->getElementData($element);

        /** @var IFormNode $node */
        foreach ($document->getIterator() as $node) {
            if ($node instanceof IFormField && $node->isAvailable()) {
                $key = $node->getId();

                if (isset($data[$key])) {
                    $node->value($data[$key]);
                } elseif ($node->getObjectProperty() !== $node->getId()) {
                    $key = $node->getObjectProperty();

                    try {
                        if (isset($data[$key])) {
                            $node->value($data[$key]);
                        }
                    } catch (\InvalidArgumentException $e) {
                        // ignore invalid argument exceptions for fields with object property
                        // as there might be multiple fields with the same object property but
                        // different possible values (for example when using single selection
                        // form fields to set the parent element)
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sets the keys of the given (empty) entry list.
     *
     * @param IDevtoolsPipEntryList $entryList
     */
    abstract protected function setEntryListKeys(IDevtoolsPipEntryList $entryList);

    /**
     * Sets the type of the currently handled pip entries.
     *
     * @param string $entryType currently handled pip entry type
     *
     * @throws  \InvalidArgumentException   if the given entry type is invalid (see `getEntryTypes()` method)
     */
    public function setEntryType($entryType)
    {
        if (!\in_array($entryType, $this->getEntryTypes())) {
            throw new \InvalidArgumentException("Unknown entry type '{$entryType}'.");
        }

        $this->entryType = $entryType;
    }

    /**
     * Returns `true` if this package installation plugin supports delete
     * instructions.
     *
     * @return  bool
     */
    public function supportsDeleteInstruction()
    {
        return true;
    }
}

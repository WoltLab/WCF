<?php
declare(strict_types=1);
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Provides default implementations of the methods of the
 * 	`wcf\system\devtools\pip\IGuiPackageInstallationPlugin`
 * interface for an xml-based package installation plugin.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since	3.2
 */
trait TXmlGuiPackageInstallationPlugin {
	/**
	 * Adds a new entry of this pip based on the data provided by the given
	 * form.
	 *
	 * @param	IFormDocument		$form
	 */
	public function addEntry(IFormDocument $form) {
		$xml = $this->getProjectXml();
		$document = $xml->getDocument();
		
		$this->writeEntry($document, $form);
		$this->sortDocument($document);
		
		/** @var DevtoolsProject $project */
		$project = $this->installation->getProject();
		
		// TODO: while creating/testing the gui, write into a temporary file
		// $xml->write($this->getXmlFileLocation($project));
		$xml->write($project->path . ($project->getPackage()->package === 'com.woltlab.wcf' ? 'com.woltlab.wcf/' : '') . 'tmp_' . static::getDefaultFilename());
	}
	
	/**
	 * Edits the entry of this pip with the given identifier based on the data
	 * provided by the given form and returns the new identifier of the entry
	 * (or the old identifier if it has not changed).
	 *
	 * @param	IFormDocument		$form
	 * @param	string			$identifier
	 * @return	string			new identifier
	 */
	public function editEntry(IFormDocument $form, string $identifier): string{
		$xml = $this->getProjectXml();
		$document = $xml->getDocument();
		
		// remove old element
		$element = $this->getElementByIdentifier($xml, $identifier);
		DOMUtil::removeNode($element);
		
		// add updated element
		$newEntry = $this->writeEntry($document, $form);
		$this->sortDocument($document);
		
		/** @var DevtoolsProject $project */
		$project = $this->installation->getProject();
		
		// TODO: while creating/testing the gui, write into a temporary file
		// $xml->write($this->getXmlFileLocation($project));
		$xml->write($project->path . ($project->getPackage()->package === 'com.woltlab.wcf' ? 'com.woltlab.wcf/' : '') . 'tmp_' . static::getDefaultFilename());
		
		return $this->getElementIdentifier($newEntry);
	}
	
	/**
	 * Checks if the given string needs to be encapsuled by cdata and does so
	 * if required.
	 * 
	 * @param	string		$value
	 * @return	string
	 */
	protected function getAutoCdataValue(string $value): string {
		if (strpos('<', $value) !== false || strpos('>', $value) !== false || strpos('&', $value) !== false) {
			$value = '<![CDATA[' . StringUtil::escapeCDATA($value) . ']]>';
		}
		
		return $value;
	}
	
	/**
	 * Returns the `import` element with the given identifier.
	 * 
	 * @param	string	$identifier
	 * @return	\DOMElement|null
	 */
	protected function getElementByIdentifier(XML $xml, string $identifier) {
		foreach ($xml->xpath()->query('/ns:data/ns:import/ns:' . $this->tagName) as $element) {
			if ($this->getElementIdentifier($element) === $identifier) {
				return $element;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the identifier of the given `import` element.
	 * 
	 * @param	\DOMElement	$element
	 * @return	string
	 */
	abstract protected function getElementIdentifier(\DOMElement $element): string;
	
	/**
	 * Returns the xml code of an empty xml file with the appropriate structure
	 * present for a new entry to be added as if it was added to an existing
	 * file.
	 * 
	 * @return	string
	 */
	protected function getEmptyXml(): string {
		$classNamePieces = explode('\\', get_class($this));
		$xsdFilename = lcfirst(str_replace('PackageInstallationPlugin', '', array_pop($classNamePieces)));
		
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/vortex/{$xsdFilename}.xsd">
	<import></import>
</data>
XML;
	}
	
	/**
	 * Returns the xml object for this pip.
	 * 
	 * @return	XML
	 */
	protected function getProjectXml() {
		$fileLocation = $this->getXmlFileLocation();
		
		$xml = new XML();
		if (!file_exists($fileLocation)) {
			$xml->loadXML($fileLocation, $this->getEmptyXml());
		}
		else {
			$xml->load($fileLocation);
		}
		
		return $xml;
	}
	
	/**
	 * Returns the location of the xml file for this pip.
	 * 
	 * @return	string
	 */
	protected function getXmlFileLocation() {
		/** @var DevtoolsProject $project */
		$project = $this->installation->getProject();
		
		return $project->path . ($project->getPackage()->package === 'com.woltlab.wcf' ? 'com.woltlab.wcf/' : '') . static::getDefaultFilename();
	}
	
	/**
	 * Adds the data of the pip entry with the given identifier into the
	 * given form and returns `true`. If no entry with the given identifier
	 * exists, `false` is returned.
	 *
	 * @param	IFormDocument		$document
	 * @param	string			$identifier
	 * @param	bool
	 */
	public function setEntryData(string $identifier, IFormDocument $document): bool {
		$xml = $this->getProjectXml();
		$xpath = $xml->xpath();
		
		$element = $this->getElementByIdentifier($xml, $identifier);
		if ($element === null) {
			return false;
		}
		
		$data = [];
		foreach ($element->childNodes as $childNode) {
			$data[$childNode->nodeName] = $childNode->nodeValue;
		}
		
		/** @var IFormNode $node */
		foreach ($document->getIterator() as $node) {
			// `data-tag` is used to map the field id to the xml element tag
			$key = $node->hasAttribute('data-tag') ? $node->getAttribute('data-tag') : $node->getId();
			
			if ($node instanceof IFormField && $node->isAvailable() && isset($data[$key])) {
				$node->value($data[$key]);
			}
		}
		
		return true;
	}
	
	/**
	 * Sorts the entries of this pip that are represented by the given dom
	 * document to achieve a deterministic order.
	 * 
	 * @param	\DOMDocument	$document
	 */
	abstract protected function sortDocument(\DOMDocument $document);
	
	/**
	 * Sorts the given child nodes of all nodes in the given node list by
	 * applying the given sort function on the child nodes.
	 * 
	 * Internally, the old child nodes are removed and appended again in
	 * the sorted order.
	 * 
	 * @param	\DOMNodeList	$nodeList
	 * @param	callable	$sortFunction
	 */
	protected function sortChildNodes(\DOMNodeList $nodeList, callable $sortFunction) {
		foreach ($nodeList as $node) {
			$childNodes = array_filter(iterator_to_array($node->childNodes), function($element) {
				return $element instanceof \DOMElement;
			});
			
			usort($childNodes, $sortFunction);
			
			// remove old nodes
			while ($node->hasChildNodes()) {
				$node->removeChild($node->firstChild);
			}
			
			// add sorted nodes
			foreach ($childNodes as $childNode) {
				$node->appendChild($childNode);
			}
		}
	}
	
	/**
	 * Sorts the standard `import` and `delete` blocks and ensures that the
	 * `import` block is before the `delete` block.
	 * 
	 * @param	\DOMDocument	$document
	 */
	protected function sortImportDelete(\DOMDocument $document) {
		switch ($document->documentElement->childNodes->length) {
			case 0:
				throw new \InvalidArgumentException('Empty xml document.');
			
			case 1:
				// nothing to sort
				break;
			
			case 2:
				$firstChild = $document->documentElement->firstChild;
				$lastChild = $document->documentElement->lastChild;
				
				if (!($firstChild->nodeName === 'import' && $lastChild->nodeName === 'delete') && !($firstChild->nodeName === 'delete' && $lastChild->nodeName === 'import')) {
					throw new \InvalidArgumentException('Invalid xml given.');
				}
				
				if ($document->documentElement->firstChild->nodeName !== 'import') {
					$firstChild = $document->documentElement->firstChild;
					$document->documentElement->removeChild($firstChild);
					$document->documentElement->appendChild($firstChild);
				}
				break;
			
			default:
				throw new \InvalidArgumentException('Xml document has more than two direct children.');
		}
	}
	
	/**
	 * Writes a new entry into the xml structure represented by the given
	 * dom document using the data provided by the given form and return
	 * the new dom element.
	 * 
	 * Note: Inserting at the correct position regarding sorting is irrelevant
	 * as the dom document will be sorted after adding the entry.
	 * 
	 * @param	\DOMDocument		$document
	 * @param	IFormDocument		$form
	 * @return	\DOMElement
	 */
	abstract protected function writeEntry(\DOMDocument $document, IFormDocument $form): \DOMElement; 
}

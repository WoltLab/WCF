<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Reads and validates xml documents.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
class XML {
	/**
	 * DOMDocument object
	 * @var	\DOMDocument
	 */
	protected $document = null;
	
	/**
	 * document path
	 * @var	string
	 */
	protected $path = '';
	
	/**
	 * schema file path
	 * @var	string
	 */
	protected $schema = '';
	
	/**
	 * DOMXPath object
	 * @var	\DOMXPath
	 */
	protected $xpath = null;
	
	/**
	 * Prepares a new instance of DOMDocument and enables own error handler for libxml.
	 */
	public function __construct() {
		libxml_use_internal_errors(true);
		$this->document = new \DOMDocument('1.0', 'UTF-8');
		$this->document->preserveWhiteSpace = false;
	}
	
	/**
	 * Loads a xml file for processing.
	 * 
	 * @param	string		$path
	 * @throws	SystemException
	 */
	public function load($path) {
		$this->path = $path;
		
		// ensure file exists and is readable
		if (!file_exists($this->path) || !is_readable($this->path)) {
			throw new SystemException("Could not read xml document located at '".$this->path."'.");
		}
		
		// flush the error buffer in case someone used global xml functions
		// without polling / clearing the buffer after use
		libxml_clear_errors();
		
		// load xml document
		$this->document->load($path);
		
		// check for errors occurred in libxml
		$errors = $this->pollErrors();
		if (!empty($errors)) {
			$this->throwException("XML document '".$this->path."' is not valid XML.", $errors);
		}
	}
	
	/**
	 * Loads a xml string, specifying $path is mandatory to provide detailed error handling.
	 * 
	 * @param	string		$path
	 * @param	string		$xml
	 */
	public function loadXML($path, $xml) {
		$this->path = $path;
		
		// flush the error buffer in case someone used global xml functions
		// without polling / clearing the buffer after use
		libxml_clear_errors();
		
		// load xml document
		$this->document->loadXML($xml);
		
		// check for errors occurred in libxml
		$errors = $this->pollErrors();
		if (!empty($errors)) {
			$this->throwException("XML document '".$this->path."' is not valid XML.", $errors);
		}
	}
	
	/**
	 * Validate the loaded document against the specified xml schema definition.
	 */
	public function validate() {
		// determine schema
		$this->getSchema();
		
		// validate document against schema
		$this->document->schemaValidate($this->schema);
		
		// check for errors occurred in libxml
		$errors = $this->pollErrors();
		if (!empty($errors)) {
			$this->throwException("XML document '".$this->path."' violates XML schema definition.", $errors);
		}
	}
	
	/**
	 * Determines schema for given document.
	 */
	protected function getSchema() {
		// determine schema by looking for xsi:schemaLocation
		$this->schema = $this->document->documentElement->getAttributeNS($this->document->documentElement->lookupNamespaceUri('xsi'), 'schemaLocation');
		
		// no valid schema found or it's lacking a valid namespace
		if (strpos($this->schema, ' ') === false) {
			throw new SystemException("XML document '".$this->path."' does not provide a valid schema.");
		}
		
		// build file path upon namespace and filename
		$tmp = explode(' ', $this->schema);
		$this->schema = WCF_DIR.'xsd/'.mb_substr(sha1($tmp[0]), 0, 8) . '_' . basename($tmp[1]);
		
		if (!file_exists($this->schema) || !is_readable($this->schema)) {
			throw new SystemException("Could not read XML schema definition located at '".$this->schema."'.");
		}
	}
	
	/**
	 * Returns a DOMXPath object bound to current DOMDocument object. Default
	 * namespace will be bound to prefix 'ns'.
	 * 
	 * @return	\DOMXPath
	 */
	public function xpath() {
		if ($this->xpath === null) {
			$this->xpath = new \DOMXPath($this->document);
			
			// register default namespace with prefix 'ns'
			$namespace = $this->document->documentElement->getAttribute('xmlns');
			$this->xpath->registerNamespace('ns', $namespace);
		}
		
		return $this->xpath;
	}
	
	/**
	 * Reads errors from libxml since be bypassed built-in error handler.
	 * 
	 * @see		\wcf\util\XML::__construct()
	 * @return	string[][]
	 */
	protected function pollErrors() {
		$errors = [];
		$errorList = libxml_get_errors();
		
		foreach ($errorList as $error) {
			$errors[] = [
				'message' => $error->message,
				'line' => $error->line,
				'file' => $this->path
			];
		}
		
		libxml_clear_errors();
		
		return $errors;
	}
	
	/**
	 * Throws a SystemException providing details on xml errors if applicable.
	 * 
	 * @param	string		$message
	 * @param	array		$errors
	 * @throws	SystemException
	 */
	protected function throwException($message, array $errors = []) {
		if (!empty($errors)) {
			$description = '<b>LibXML output:</b><pre>';
			foreach ($errors as $error) {
				$description .= "#".$error['line']."\t".$error['message'];
			}
			$description .= '</pre>';
			
			throw new SystemException($message, 0, $description);
		}
		else {
			throw new SystemException($message);
		}
	}
	
	/**
	 * Returns the dom document object this object is working with.
	 * 
	 * @return	\DOMDocument
	 * @since	3.2
	 */
	public function getDocument() {
		return $this->document;
	}
	
	/**
	 * Writes the xml structure into the given file.
	 * 
	 * @param	string		$fileLocation	location of file
	 * @param	bool		$cdata		indicates of values are escaped using cdata
	 * @since	3.2
	 */
	public function write($fileLocation, $cdata = false) {
		$schemaParts = explode(' ', $this->document->documentElement->getAttributeNS($this->document->documentElement->lookupNamespaceUri('xsi'), 'schemaLocation'));
		
		$writer = new XMLWriter();
		$writer->beginDocument(
			$this->document->documentElement->nodeName,
			$schemaParts[0],
			$schemaParts[1],
			$this->getAttributes($this->document->documentElement)
		);
		foreach ($this->document->documentElement->childNodes as $childNode) {
			$this->writeElement($writer, $childNode, $cdata);
		}
		$writer->endDocument($fileLocation);
	}
	
	/**
	 * Writes the given element using the given xml writer.
	 * 
	 * @param	XMLWriter	$writer		xml writer
	 * @param	\DOMElement	$element	written element
	 * @param	bool		$cdata		indicates if element value is escaped using cdata
	 * @since	3.2
	 */
	protected function writeElement(XMLWriter $writer, \DOMElement $element, $cdata) {
		if ($element->childNodes->length === 1 && $element->firstChild instanceof \DOMText) {
			$writer->writeElement(
				$element->nodeName,
				$element->firstChild->nodeValue,
				$this->getAttributes($element),
				$cdata || $element->firstChild instanceof \DOMCdataSection
			);
		}
		else {
			$writer->startElement($element->nodeName, $this->getAttributes($element));
			foreach ($element->childNodes as $childNode) {
				// only consider dom elements, ignore comments
				if ($childNode instanceof \DOMElement) {
					$this->writeElement($writer, $childNode, $cdata);
				}
			}
			$writer->endElement();
		}
	}
	
	/**
	 * Returns an array with the attribute values of the given dom element
	 * (with the attribute names as array keys).
	 * 
	 * @param	\DOMElement	$element	elements whose attributes will be returned
	 * @return	array				attributes
	 * @since	3.2
	 */
	protected function getAttributes(\DOMElement $element) {
		$attributes = [];
		/** @var \DOMNode $attribute */
		foreach ($element->attributes as $attribute) {
			$attributes[$attribute->nodeName] = $attribute->nodeValue;
		}
		
		return $attributes;
	}
	
	/**
	 * Returns the path to the xml file.
	 * 
	 * @return	string
	 */
	public function getPath() {
		return $this->path;
	}
}

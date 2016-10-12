<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Reads and validates xml documents.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
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
		
		// load xml document
		$this->document->load($path);
		
		// check for errors occured in libxml
		$errors = $this->pollErrors();
		if (!empty($errors)) {
			$this->throwException("XML document '".$this->path."' is not valid XML.", $errors);
		}
	}
	
	/**
	 * Loads a xml string, specifying $path is mandatory to provide detailied error handling.
	 * 
	 * @param	string		$path
	 * @param	string		$xml
	 */
	public function loadXML($path, $xml) {
		$this->path = $path;
		
		// load xml document
		$this->document->loadXML($xml);
		
		// check for errors occured in libxml
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
		
		// check for errors occured in libxml
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
}

<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Writes XML documents.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
class XMLWriter {
	/**
	 * true, if a document is still processed
	 * @var	boolean
	 */
	protected $activeDocument = false;
	
	/**
	 * number of open elments
	 * @var	integer
	 */
	protected $openElements = 0;
	
	/**
	 * XMLWriter object
	 * @var	\XMLWriter
	 */
	protected $xml = null;
	
	/**
	 * Creates a new XML document.
	 * 
	 * @param	string		$rootElement
	 * @param	string		$namespace
	 * @param	string		$schemaLocation
	 * @param	string[]	$attributes
	 * @throws	SystemException
	 */
	public function beginDocument($rootElement, $namespace, $schemaLocation, array $attributes = []) {
		if ($this->activeDocument) {
			throw new SystemException("Could not begin a new document unless the previous is finished");
		}
		
		if ($this->xml === null) {
			$this->xml = new \XMLWriter();
			$this->xml->openMemory();
			$this->xml->setIndent(true);
			$this->xml->setIndentString("\t");
		}
		
		$this->xml->startDocument('1.0', 'UTF-8');
		$this->startElement($rootElement);
		$attributes = array_merge($attributes, [
			'xmlns' => $namespace,
			'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation' => $namespace . ' ' . $schemaLocation
		]);
		$this->writeAttributes($attributes);
		
		$this->activeDocument = true;
	}
	
	/**
	 * Returns the generated XML document or writes it to given filename. All open
	 * elements will be automatically closed before flushing.
	 * 
	 * @param	string		$filename
	 * @return	mixed
	 */
	public function endDocument($filename = '') {
		// mark document as done
		$this->activeDocument = false;
		
		// close all open tags
		while ($this->openElements) {
			$this->endElement();
		}
		
		if (empty($filename)) {
			// return XML as string
			return $this->xml->flush(true);
		}
		else {
			// write to file
			file_put_contents($filename, $this->xml->flush(true));
		}
	}
	
	/**
	 * Begins a new element.
	 * 
	 * @param	string		$element
	 * @param	string[]	$attributes
	 */
	public function startElement($element, array $attributes = []) {
		$this->xml->startElement($element);
		$this->openElements++;
		
		if (!empty($attributes)) {
			$this->writeAttributes($attributes);
		}
	}
	
	/**
	 * Ends the last opened element.
	 */
	public function endElement() {
		if ($this->openElements) {
			$this->xml->endElement();
			$this->openElements--;
		}
	}
	
	/**
	 * Writes an element directly.
	 * 
	 * @param	string		$element
	 * @param	string		$cdata
	 * @param	string[]	$attributes
	 */
	public function writeElement($element, $cdata, array $attributes = []) {
		$this->startElement($element);
		
		// write attributes
		if (!empty($attributes)) {
			$this->writeAttributes($attributes);
		}
		
		// content
		$this->xml->writeCdata(StringUtil::escapeCDATA($cdata));
		
		$this->endElement();
	}
	
	/**
	 * Writes an attribute to last opened element.
	 * 
	 * @param	string		$attribute
	 * @param	string		$value
	 */
	public function writeAttribute($attribute, $value) {
		$this->writeAttributes([$attribute => $value]);
	}
	
	/**
	 * Writes a list of attributes to last opened element.
	 * 
	 * @param	string[]		$attributes
	 */
	public function writeAttributes(array $attributes) {
		foreach ($attributes as $attribute => $value) {
			$this->xml->writeAttribute($attribute, $value);
		}
	}
}

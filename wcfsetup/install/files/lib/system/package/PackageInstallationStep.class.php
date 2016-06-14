<?php
namespace wcf\system\package;
use wcf\system\form\FormDocument;

/**
 * Represents step information within an installation node.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageInstallationStep {
	/**
	 * form document object
	 * @var	\wcf\system\form\FormDocument
	 */
	protected $document = null;
	
	/**
	 * next installation node
	 * @var	string
	 */
	protected $node = '';
	
	/**
	 * indicates if current current node should be splitted
	 * @var	boolean
	 */
	protected $splitNode = false;
	
	/**
	 * Sets next installation node.
	 * 
	 * @param	string		$node
	 */
	public function setNode($node) {
		$this->node = $node;
	}
	
	/**
	 * Returns next installation node.
	 * 
	 * @return	string
	 */
	public function getNode() {
		return $this->node;
	}
	
	/**
	 * Sets form document object.
	 * 
	 * @param	FormDocument	$document
	 */
	public function setDocument(FormDocument $document) {
		$this->document = $document;
	}
	
	/**
	 * Returns HTML-representation of form document object.
	 * 
	 * @return	string
	 */
	public function getTemplate() {
		return $this->document->getHTML();
	}
	
	/**
	 * Returns true if current step holds a form document object.
	 * 
	 * @return	boolean
	 */
	public function hasDocument() {
		return ($this->document !== null);
	}
	
	/**
	 * Enforces node splitting.
	 */
	public function setSplitNode() {
		$this->splitNode = true;
	}
	
	/**
	 * Returns true if node should be splitted.
	 * 
	 * @return	boolean
	 */
	public function splitNode() {
		return $this->splitNode;
	}
}

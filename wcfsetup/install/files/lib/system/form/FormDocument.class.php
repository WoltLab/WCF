<?php
namespace wcf\system\form;
use wcf\util\StringUtil;

/**
 * FormDocument holds the page structure based upon form element containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form
 */
class FormDocument {
	/**
	 * list of FormElementContainer objects
	 * @var	IFormElementContainer[]
	 */
	protected $containers = [];
	
	/**
	 * form document name
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * Creates a new instance of FormDocument.
	 * 
	 * @param	string		$name
	 */
	public function __construct($name) {
		$this->name = StringUtil::trim($name);
	}
	
	/**
	 * Returns form document name.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Appends a FormElementContainer object.
	 * 
	 * @param	\wcf\system\form\IFormElementContainer		$container
	 */
	public function appendContainer(IFormElementContainer $container) {
		$this->containers[] = $container;
	}
	
	/**
	 * Prepends a FormElementContainer object.
	 * 
	 * @param	\wcf\system\form\IFormElementContainer		$container
	 */
	public function prependContainer(IFormElementContainer $container) {
		array_unshift($this->containers, $container);
	}
	
	/**
	 * Returns assigned FormElementContainer objects.
	 * 
	 * @return	IFormElementContainer[]
	 */
	public function getContainers() {
		return $this->containers;
	}
	
	/**
	 * Returns the value of container's child element with given name.
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function getValue($key) {
		foreach ($this->containers as $container) {
			$value = $container->getValue($key);
			if ($value !== null) {
				return $value;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns HTML-representation of current document.
	 * 
	 * @return	string
	 */
	public function getHTML() {
		$content = '';
		
		foreach ($this->containers as $container) {
			$content .= $container->getHTML($this->getName().'_');
		}
		
		return $content;
	}
	
	/**
	 * Handles request input variables.
	 */
	public function handleRequest() {
		$variables = [];
		
		foreach ($_REQUEST as $key => $value) {
			if (mb_strpos($key, $this->getName().'_') !== false) {
				$key = str_replace($this->getName().'_', '', $key);
				$variables[$key] = $value;
			}
		}
		
		if (!empty($variables)) {
			foreach ($this->containers as $container) {
				$container->handleRequest($variables);
			}
		}
	}
	
	/**
	 * Sets localized error message for given element.
	 * 
	 * @param	string		$name
	 * @param	string		$error
	 */
	public function setError($name, $error) {
		foreach ($this->containers as $container) {
			$container->setError($name, $error);
		}
	}
}

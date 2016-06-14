<?php
namespace wcf\system\form\container;
use wcf\system\form\element\AbstractNamedFormElement;
use wcf\system\form\IFormElement;
use wcf\system\form\IFormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form element containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Container
 */
abstract class AbstractFormElementContainer implements IFormElementContainer {
	/**
	 * list of IFormElement objects
	 * @var	IFormElement[]
	 */
	protected $children = [];
	
	/**
	 * element description or help text
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * element label
	 * @var	string
	 */
	protected $label = '';
	
	/**
	 * @inheritDoc
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @inheritDoc
	 */
	public function appendChild(IFormElement $element) {
		$this->children[] = $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function prependChild(IFormElement $element) {
		array_unshift($this->children, $element);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getValue($key) {
		foreach ($this->children as $element) {
			if ($element instanceof AbstractNamedFormElement) {
				if ($element->getName() == $key) {
					return $element->getValue();
				}
			}
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function handleRequest(array $variables) {
		foreach ($this->children as $element) {
			if (!($element instanceof AbstractNamedFormElement)) {
				continue;
			}
			
			if (isset($variables[$element->getName()])) {
				$element->setValue($variables[$element->getName()]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function setError($name, $error) {
		foreach ($this->children as $element) {
			if (!($element instanceof AbstractNamedFormElement)) {
				continue;
			}
			
			if ($element->getName() == $name) {
				$element->setError($error);
			}
		}
	}
}

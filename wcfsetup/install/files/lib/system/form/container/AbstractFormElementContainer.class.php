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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category	Community Framework
 */
abstract class AbstractFormElementContainer implements IFormElementContainer {
	/**
	 * list of IFormElement objects
	 * @var	array<\wcf\system\form\IFormElement>
	 */
	protected $children = array();
	
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
	 * @see	\wcf\system\form\IFormElementContainer::setDescription()
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getDescription()
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::setLabel()
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getLabel()
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::appendChild()
	 */
	public function appendChild(IFormElement $element) {
		$this->children[] = $element;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::prependChild()
	 */
	public function prependChild(IFormElement $element) {
		array_unshift($this->children, $element);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getChildren()
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getValue()
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
	 * @see	\wcf\system\form\IFormElementContainer::handleRequest()
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
	 * @see	\wcf\system\form\IFormElementContainer::setError()
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

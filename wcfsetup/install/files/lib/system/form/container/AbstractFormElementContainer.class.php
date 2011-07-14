<?php
namespace wcf\system\form\container;
use wcf\system\form\FormElement;
use wcf\system\form\FormElementContainer;
use wcf\system\form\element\AbstractNamedFormElement;
use wcf\util\StringUtil;

/**
 * Basic implementation for form element containers.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
abstract class AbstractFormElementContainer implements FormElementContainer {
	/**
	 * list of FormElement objects
	 *
	 * @var	array<FormElement>
	 */
	protected $children = array();
	
	/**
	 * element description or help text
	 *
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * element label
	 *
	 * @var	string
	 */
	protected $label = '';
	
	/**
	 * @see	FormElementContainer::setDescription()
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @see	FormElementContainer::getDescription()
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	FormElementContainer::setLabel()
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @see	FormElementContainer::getLabel()
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @see	FormElementContainer::appendChild()
	 */
	public function appendChild(FormElement $element) {
		$this->children[] = $element;
	}
	
	/**
	 * @see	FormElementContainer::prependChild()
	 */
	public function prependChild(FormElement $element) {
		array_unshift($this->children, $element);
	}
	
	/**
	 * @see	FormElementContainer::getChildren()
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * @see	FormElementContainer::getValue()
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
	 * @see	FormElementContainer::handleRequest()
	 */
	public function handleRequest(array $variables) {
		foreach ($this->children as $element) {
			if (!($element instanceof AbstractNamedFormElement)) {
				return;
			}
			
			if (isset($variables[$element->getName()])) {
				$element->setValue($variables[$element->getName()]);
			}
		}
	}
}

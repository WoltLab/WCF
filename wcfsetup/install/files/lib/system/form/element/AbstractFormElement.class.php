<?php
namespace wcf\system\form\element;
use wcf\system\form\IFormElement;
use wcf\system\form\IFormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form elements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
abstract class AbstractFormElement implements IFormElement {
	/**
	 * element description or help text
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * localized error string
	 * @var	string
	 */
	protected $error = '';
	
	/**
	 * element label
	 * @var	string
	 */
	protected $label = '';
	
	/**
	 * FormElementContainer object
	 * @var	\wcf\system\form\IFormElementContainer
	 */
	protected $parent = null;
	
	/**
	 * @see	\wcf\system\form\IFormElement::__construct()
	 */
	public function __construct(IFormElementContainer $parent) {
		$this->parent = $parent;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::setDescription()
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::getDescription()
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::setLabel()
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::getLabel()
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::getParent()
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::setError()
	 */
	public function setError($error) {
		$this->error = $error;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::getError()
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * Returns class attribute if an error occured.
	 * 
	 * @return	string
	 */
	protected function getErrorClass() {
		return ($this->getError()) ? ' class="formError"' : '';
	}
	
	/**
	 * Returns an error message if occured.
	 * 
	 * @return	string
	 */
	protected function getErrorField() {
		return ($this->getError()) ? '<small class="innerError">'.$this->getError().'</small>' : '';
	}
}

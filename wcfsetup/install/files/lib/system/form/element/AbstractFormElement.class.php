<?php
namespace wcf\system\form\element;
use wcf\system\form\FormElement;
use wcf\system\form\FormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form elements.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
abstract class AbstractFormElement implements FormElement {
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
	 * FormElementContainer object
	 *
	 * @var	FormElementContainer
	 */
	protected $parent = null;
	
	/**
	 * @see	FormElement::__construct()
	 */
	public function __construct(FormElementContainer $parent) {
		$this->parent = $parent;
	}
	
	/**
	 * @see	FormElement::setDescription()
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @see	FormElement::getDescription()
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	FormElement::setLabel()
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @see	FormElement::getLabel()
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @see	FormElement::getParent()
	 */
	public function getParent() {
		return $this->parent;
	}
}

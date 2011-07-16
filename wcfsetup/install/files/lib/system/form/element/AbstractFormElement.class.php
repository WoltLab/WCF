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
	 * @see	wcf\system\form\FormElement::__construct()
	 */
	public function __construct(FormElementContainer $parent) {
		$this->parent = $parent;
	}
	
	/**
	 * @see	wcf\system\form\FormElement::setDescription()
	 */
	public function setDescription($description) {
		$this->description = StringUtil::trim($description);
	}
	
	/**
	 * @see	wcf\system\form\FormElement::getDescription()
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	wcf\system\form\FormElement::setLabel()
	 */
	public function setLabel($label) {
		$this->label = StringUtil::trim($label);
	}
	
	/**
	 * @see	wcf\system\form\FormElement::getLabel()
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @see	wcf\system\form\FormElement::getParent()
	 */
	public function getParent() {
		return $this->parent;
	}
}

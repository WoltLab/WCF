<?php
namespace wcf\system\form\element;
use wcf\system\form\IFormElement;
use wcf\system\form\IFormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form elements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Element
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
	 * @inheritDoc
	 */
	public function __construct(IFormElementContainer $parent) {
		$this->parent = $parent;
	}
	
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
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setError($error) {
		$this->error = $error;
	}
	
	/**
	 * @inheritDoc
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

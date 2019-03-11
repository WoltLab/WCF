<?php
namespace wcf\system\form\builder\field\dependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;
use wcf\system\WCF;

/**
 * Abstract implementation of a form field dependency.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since	5.2
 */
abstract class AbstractFormFieldDependency implements IFormFieldDependency {
	/**
	 * node whose availability depends on the value of a field
	 * @var	IFormNode
	 */
	protected $dependentNode;
	
	/**
	 * field the availability of the node dependents on
	 * @var	IFormField
	 */
	protected $field;
	
	/**
	 * id of the dependency
	 * @var	string
	 */
	protected $id;
	
	/**
	 * name of the template containing the dependency JavaScript code
	 * @var	null|string
	 */
	protected $templateName;
	
	/**
	 * @inheritDoc
	 */
	public function dependentNode(IFormNode $node) {
		$this->dependentNode = $node;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function field(IFormField $field) {
		$this->field = $field;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDependentNode() {
		if ($this->dependentNode === null) {
			throw new \BadMethodCallException("Dependent node has not been set.");
		}
		
		return $this->dependentNode;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getField() {
		if ($this->field === null) {
			throw new \BadMethodCallException("Field has not been set.");
		}
		
		return $this->field;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		if ($this->templateName === null) {
			throw new \LogicException("Template name is not set.");
		}
		
		return WCF::getTPL()->fetch($this->templateName, 'wcf', [
			'dependency' => $this
		], true);
	}
	
	/**
	 * Sets the id of this dependency and returns this dependency.
	 * 
	 * @param	string		$id		id of the dependency
	 * @return	static		$this		this dependency
	 * 
	 * @throws	\InvalidArgumentException	if given id no or otherwise invalid
	 */
	protected function id($id) {
		if (preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
		
		$this->id = $id;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public static function create($id) {
		return (new static)->id($id);
	}
}

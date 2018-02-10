<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field\dependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormNode;
use wcf\system\WCF;

/**
 * Abstract implementation of a form field dependency.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Dependency
 * @since	3.2
 */
abstract class AbstractFormFieldDependency implements IFormFieldDependency {
	/**
	 * node whose availability depends on the value of a field
	 * @var	IFormNode
	 */
	protected $__dependentNode;
	
	/**
	 * field the availability of the node dependents on
	 * @var	IFormField
	 */
	protected $__field;
	
	/**
	 * id of the dependency
	 * @var	string
	 */
	protected $__id;
	
	/**
	 * name of the template containing the dependency JavaScript code
	 * @var	null|string
	 */
	protected $templateName;
	
	/**
	 * @inheritDoc
	 */
	public function dependentNode(IFormNode $node): IFormFieldDependency {
		$this->__dependentNode = $node;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function field(IFormField $field): IFormFieldDependency {
		$this->__field = $field;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDependentNode(): IFormNode {
		if ($this->__dependentNode === null) {
			throw new \BadMethodCallException("Dependent node has not been set.");
		}
		
		return $this->__dependentNode;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getField(): IFormField {
		if ($this->__field === null) {
			throw new \BadMethodCallException("Field has not been set.");
		}
		
		return $this->__field;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->__id;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml(): string {
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
	 * @throws	\InvalidArgumentException	if given id no string or otherwise invalid
	 */
	protected function id(string $id): IFormFieldDependency {
		if (preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
			throw new \InvalidArgumentException("Invalid id '{$id}' given.");
		}
		
		$this->__id = $id;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function create(string $id): IFormFieldDependency {
		return (new static)->id($id);
	}
}

<?php
namespace wcf\system\form\builder\container;
use wcf\data\IStorableObject;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormElement;
use wcf\system\form\builder\TFormParentNode;
use wcf\system\WCF;

/**
 * Represents a default container.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
class FormContainer implements IFormContainer {
	use TFormChildNode;
	use TFormElement;
	use TFormParentNode {
		TFormParentNode::cleanup insteadof TFormElement;
		validateChild as protected defaultValidateChild;
	}
	
	/**
	 * name of container template
	 * @var	string
	 */
	protected $templateName = '__formContainer';
	
	/**
	 * name of the template's application used to output this container
	 * @var	string
	 */
	protected $templateApplication = 'wcf';
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$this->addClass('section');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch(
			$this->templateName,
			$this->templateApplication,
			array_merge($this->getHtmlVariables(), [
				'container' => $this
			]),
			true
		);
	}
	
	/**
	 * @inheritDoc
	 * @since	5.3
	 */
	public function markAsRequired() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatedObject(array $data, IStorableObject $object, $loadValues = true) {
		// does nothing
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateChild(IFormChildNode $child) {
		$this->defaultValidateChild($child);
		
		if ($this instanceof ITabMenuFormContainer) {
			if (!($child instanceof ITabFormContainer)) {
				throw new \InvalidArgumentException("Cannot append non-tab container ".get_class($child)."('{$child->getId()}') to tab menu container '{$this->getId()}'");
			}
			
			if ($child instanceof ITabMenuFormContainer) {
				if ($this->getParent() instanceof ITabMenuFormContainer) {
					throw new \InvalidArgumentException("Tab menus can only be nested once.");
				}
			}
		}
		else if ($child instanceof ITabFormContainer) {
			throw new \InvalidArgumentException("Cannot append tab container '{$child->getId()}' to non-tab menu container '{$this->getId()}'.");
		}
		
		if ($this instanceof ITabFormContainer && !($child instanceof IFormContainer)) {
			throw new \InvalidArgumentException("Child ".get_class($child)."('{$child->getId()}') has to be a form container to be appended to tab container '{$this->getId()}'.");
		}
		
		if ($child instanceof ITabMenuFormContainer) {
			$parent = $this;
			while (!($parent instanceof IFormDocument) && $parent = $parent->getParent()) {
				if ($parent instanceof ITabMenuFormContainer) {
					throw new \InvalidArgumentException("A tab menu container may only have another tab menu container as a parent, not as an earlier ancestor.");
				}
			}
		}
	}
}

<?php
namespace wcf\system\form\builder;
use wcf\data\IStorableObject;
use wcf\system\form\builder\field\data\DefaultFormFieldDataProcessor;
use wcf\system\form\builder\field\IFileFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\WCF;

/**
 * Represents a "whole" form (document).
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
class FormDocument implements IFormDocument {
	use TFormNode;
	use TFormParentNode;
	
	/**
	 * `action` property of the HTML `form` element
	 * @var	string
	 */
	protected $__action;
	
	/**
	 * `method` property of the HTML `form` element
	 * @var	string
	 */
	protected $__method = 'post';
	
	/**
	 * global form prefix that is prepended to form elements' names and ids to
	 * avoid conflicts with other forms
	 * @var	string
	 */
	protected $__prefix;
	
	/**
	 * data handler for this document
	 * @var	IFormDataHandler
	 */
	protected $dataHandler;
	
	/**
	 * encoding type of this form
	 * @var	null|string 
	 */
	protected $enctype = '';
	
	/**
	 * is `true` if form document has already been built and is `false` otherwise
	 * @var	bool
	 */
	protected $isBuilt = false;
	
	/**
	 * @inheritDoc
	 */
	public function action(string $action): IFormDocument {
		$this->__action = $action;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function build(): IFormDocument {
		if ($this->isBuilt) {
			throw new \BadMethodCallException("Form document has already been built.");
		}
		
		$nodeIds = [];
		$doubleNodeIds = [];
		
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if (in_array($node->getId(), $nodeIds)) {
				$doubleNodeIds[] = $node->getId();
			}
			else {
				$nodeIds[] = $node->getId();
			}
			
			$node->populate();
		}
		
		if (!empty($doubleNodeIds)) {
			throw new \LogicException("Non-unique node id" . (count($doubleNodeIds) > 1 ? 's' : '') . " '" . implode("', '", $doubleNodeIds) . "'.");
		}
		
		$this->isBuilt = true;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAction(): string {
		if ($this->__action === null) {
			throw new \BadMethodCallException("Action has not been set.");
		}
		
		return $this->__action;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(): array {
		return $this->getDataHandler()->getData($this);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDataHandler(): IFormDataHandler {
		if ($this->dataHandler === null) {
			$this->dataHandler = new FormDataHandler();
			$this->dataHandler->add(new DefaultFormFieldDataProcessor());
		}
		
		return $this->dataHandler;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDocument(): IFormDocument {
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEnctype() {
		if ($this->enctype === '') {
			/** @var IFormNode $node */
			foreach ($this->getIterator() as $node) {
				if ($node instanceof IFileFormField) {
					$this->enctype = 'multipart/form-data';
					
					return $this->enctype;
				}
			}
			
			$this->enctype = null;
		}
		
		return $this->enctype;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml(): string {
		return WCF::getTPL()->fetch('__form', 'wcf', array_merge($this->getHtmlVariables(), [
			'form' => $this
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMethod(): string {
		return $this->__method;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValuesFromObject(IStorableObject $object): IFormDocument {
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if ($node instanceof IFormField && $node->isAvailable()) {
				$node->loadValueFromObject($object);
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPrefix(): string {
		if ($this->__prefix === null) {
			return '';
		}
		
		return $this->__prefix . '_';
	}
	
	/**
	 * @inheritDoc
	 */
	public function method(string $method): IFormDocument {
		if ($method !== 'get' && $method !== 'post') {
			throw new \InvalidArgumentException("Invalid method '{$method}' given.");
		}
		
		$this->__method = $method;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function prefix(string $prefix): IFormDocument {
		static::validateId($prefix);
		
		$this->__prefix = $prefix;
		
		return $this;
	}
}

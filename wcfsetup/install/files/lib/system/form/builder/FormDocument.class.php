<?php
namespace wcf\system\form\builder;
use wcf\data\IStorableObject;
use wcf\system\form\builder\data\FormDataHandler;
use wcf\system\form\builder\data\IFormDataHandler;
use wcf\system\form\builder\field\data\processor\DefaultFormFieldDataProcessor;
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
 * @since	5.2
 */
class FormDocument implements IFormDocument {
	use TFormNode;
	use TFormParentNode {
		TFormParentNode::cleanup insteadof TFormNode;
		readValues as protected defaultReadValues;
	}
	
	/**
	 * `action` property of the HTML `form` element
	 * @var	string
	 */
	protected $__action;
	
	/**
	 * form mode (see `self::FORM_MODE_*` constants)
	 * @var	null|string
	 */
	protected $__formMode;
	
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
	 * request data of the form's field
	 * @var	null|array
	 */
	protected $__requestData;
	
	/**
	 * data handler for this document
	 * @var	IFormDataHandler
	 */
	protected $dataHandler;
	
	/**
	 * encoding type of this form
	 * @var	null|
	 */
	protected $enctype = '';
	
	/**
	 * is `true` if form document has already been built and is `false` otherwise
	 * @var	bool
	 */
	protected $isBuilt = false;
	
	/**
	 * Cleans up the form document before the form document object is destroyed.
	 */
	public function __destruct() {
		$this->cleanup();
	}
	
	/**
	 * @inheritDoc
	 */
	public function action($action) {
		$this->__action = $action;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function build() {
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
	public function formMode($formMode) {
		if ($this->__formMode !== null) {
			throw new \BadMethodCallException("Form mode has already been set");
		}
		
		if ($formMode !== self::FORM_MODE_CREATE && $formMode !== self::FORM_MODE_UPDATE) {
			throw new \InvalidArgumentException("Unknown form mode '{$formMode}' given.");
		}
		
		$this->__formMode = $formMode;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAction() {
		if ($this->__action === null) {
			throw new \BadMethodCallException("Action has not been set.");
		}
		
		return $this->__action;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		return $this->getDataHandler()->getData($this);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDataHandler() {
		if ($this->dataHandler === null) {
			$this->dataHandler = new FormDataHandler();
			$this->dataHandler->add(new DefaultFormFieldDataProcessor());
		}
		
		return $this->dataHandler;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDocument() {
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
	public function getFormMode() {
		if ($this->__formMode === null) {
			$this->__formMode = self::FORM_MODE_CREATE;
		}
		
		return $this->__formMode;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch(
			'__form',
			'wcf',
			array_merge($this->getHtmlVariables(), ['form' => $this])
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMethod() {
		return $this->__method;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPrefix() {
		if ($this->__prefix === null) {
			return '';
		}
		
		return $this->__prefix . '_';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRequestData($index = null) {
		if ($this->__requestData === null) {
			$this->__requestData = $_POST;
		}
		
		if ($index !== null) {
			if (!isset($this->__requestData[$index])) {
				throw new \InvalidArgumentException("Unknown request data with index '" . $index . "'.");
			}
			
			return $this->__requestData[$index];
		}
		
		return $this->__requestData;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasRequestData($index = null) {
		$requestData = $this->getRequestData();
		
		if ($index !== null) {
			return isset($requestData[$index]);
		}
		
		return !empty($requestData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValuesFromObject(IStorableObject $object) {
		if ($this->__formMode === null) {
			$this->formMode(self::FORM_MODE_UPDATE);
		}
		
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if ($node instanceof IFormField && $node->isAvailable()) {
				if ($node->getObjectProperty() !== $node->getId()) {
					try {
						$node->loadValueFromObject($object);
					}
					catch (\InvalidArgumentException $e) {
						// if an object property is explicitly set,
						// ignore invalid values as this might not be
						// the appropriate field
					}
				}
				else {
					$node->loadValueFromObject($object);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function method($method) {
		if ($method !== 'get' && $method !== 'post') {
			throw new \InvalidArgumentException("Invalid method '{$method}' given.");
		}
		
		$this->__method = $method;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function prefix($prefix) {
		static::validateId($prefix);
		
		$this->__prefix = $prefix;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValues() {
		if ($this->__requestData === null) {
			$this->__requestData = $_POST;
		}
		
		return $this->defaultReadValues();
	}
	
	/**
	 * @inheritDoc
	 */
	public function requestData(array $requestData) {
		if ($this->__requestData !== null) {
			throw new \BadMethodCallException('Request data has already been set.');
		}
		
		$this->__requestData = $requestData;
		
		return $this;
	}
}

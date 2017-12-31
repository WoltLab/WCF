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
	 * @inheritDoc
	 */
	public function action($action) {
		if (!is_string($action)) {
			throw new \InvalidArgumentException("Given action is no string, '" . gettype($action) . "' given.");
		}
		
		$this->__action = $action;
		
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
	public function getHtml() {
		return WCF::getTPL()->fetch('__form', 'wcf', [
			'form' => $this
		]);
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
	public function loadValuesFromObject(IStorableObject $object) {
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if ($node instanceof IFormField) {
				$node->loadValueFromObject($object);
			}
		}
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
	public function method($method) {
		if (!is_string($method)) {
			throw new \InvalidArgumentException("Given method is no string, " . gettype($method) . " given.");
		}
		
		if ($method !== 'get' && $method !== 'post') {
			throw new \InvalidArgumentException("Invalid method '{$method}' given.");
		}
		
		$this->__method = $method;
	}
	
	/**
	 * @inheritDoc
	 */
	public function prefix($prefix) {
		static::validateId($prefix);
		
		$this->__prefix = $prefix;
	}
}

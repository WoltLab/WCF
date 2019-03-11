<?php
namespace wcf\system\form\builder\field\acl\simple;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\data\processor\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for setting simple acl.
 * 
 * Note: This form field should not be put in a simple `FormContainer` element
 * as its output already generates `.section` elements.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Acl\Simple
 * @since	5.2
 */
class SimpleAclFormField extends AbstractFormField {
	/**
	 * @inheritDoc
	 */
	protected $templateName = 'aclSimple';
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables() {
		return [
			'__aclSimplePrefix' => $this->getPrefixedId(),
			'__aclInputName' => $this->getPrefixedId(),
			'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->getValue() ?: [])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('i18n', function(IFormDocument $document, array $parameters) {
			if ($this->checkDependencies() && is_array($this->getValue()) && !empty($this->getValue())) {
				$parameters[$this->getObjectProperty()] = $this->getValue();
			}
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_array($value)) {
				$this->value = $value;
			}
		}
		
		return $this;
	}
}

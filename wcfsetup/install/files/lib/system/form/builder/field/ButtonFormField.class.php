<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Implementation of a form field for submit buttons.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
class ButtonFormField extends AbstractFormField {
	/**
	 * text shown on the button
	 * @var ?string
	 */
	protected $buttonLabel;
	
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Button';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__buttonFormField';
	
	/**
	 * Sets the text shown on the button and returns this form field.
	 */
	public function buttonLabel(string $languageItem, array $variables = []): self {
		$this->buttonLabel = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		
		return $this;
	}
	
	/**
	 * Returns the text shown on the button.
	 */
	public function getButtonLabel(): string {
		if ($this->buttonLabel === null) {
			throw new \BadMethodCallException("Button label has not been set.");
		}
		
		return $this->buttonLabel;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		if ($this->buttonLabel === null) {
			throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a button label.");
		}
		
		return parent::getHtml();
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
		
		$this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor('acl', function(IFormDocument $document, array $parameters) {
			if (!isset($parameters[$this->getObjectProperty()])) {
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
		// The value of the button is set when setting up the form and has to be unset
		// if the button was not clicked.
		if (!$this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$this->value = null;
		}
		
		return $this;
	}
}

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
 * @since       5.4
 */
class ButtonFormField extends AbstractFormField implements IAttributeFormField, ICssClassFormField
{
    use TInputAttributeFormField;
    use TCssClassFormField;

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
    protected $templateName = 'shared_buttonFormField';

    /**
     * Sets the text shown on the button and returns this form field.
     *
     * @return  ButtonFormField     this form field
     */
    public function buttonLabel(string $languageItem, array $variables = [])
    {
        $this->buttonLabel = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);

        return $this;
    }

    /**
     * Returns the text shown on the button.
     */
    public function getButtonLabel(): string
    {
        if ($this->buttonLabel === null) {
            throw new \BadMethodCallException("Button label has not been set for field '{$this->getId()}'.");
        }

        return $this->buttonLabel;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        if ($this->buttonLabel === null) {
            throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a button label.");
        }

        return parent::getHtml();
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'button',
            function (IFormDocument $document, array $parameters) {
                if (!isset($parameters[$this->getObjectProperty()]) && $this->getValue() !== null) {
                    $parameters[$this->getObjectProperty()] = $this->getValue();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        // The value of the button is set when setting up the form and has to be unset
        // if the button was not clicked.
        if (!$this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = null;
        }

        return $this;
    }
}

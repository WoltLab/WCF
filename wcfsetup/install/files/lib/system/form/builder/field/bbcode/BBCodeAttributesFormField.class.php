<?php

namespace wcf\system\form\builder\field\bbcode;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\Regex;

/**
 * Implementation of a form field for the attributes of a form field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class BBCodeAttributesFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_bbcodeAttributesFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'attributes';
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        foreach ($this->getValue() as $attributeNumber => $attributeData) {
            if (!empty($attributeData['attributeHtml']) && \mb_strlen($attributeData['attributeHtml']) > 255) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        $this->getPrefixedId() . '_attributeHtml_' . $attributeNumber,
                        'wcf.form.field.text.error.maximumLength',
                        [
                            'length' => \mb_strlen($attributeData['attributeHtml']),
                            'maximumLength' => 255,
                        ]
                    )
                );
            }
            if (!empty($attributeData['validationPattern'])) {
                if (\mb_strlen($attributeData['validationPattern']) > 255) {
                    $this->addValidationError(
                        new FormFieldValidationError(
                            $this->getPrefixedId() . '_validationPattern_' . $attributeNumber,
                            'wcf.form.field.text.error.maximumLength',
                            [
                                'length' => \mb_strlen($attributeData['validationPattern']),
                                'maximumLength' => 255,
                            ]
                        )
                    );
                } elseif (!Regex::compile($attributeData['validationPattern'])->isValid()) {
                    $this->addValidationError(
                        new FormFieldValidationError(
                            $this->getPrefixedId() . '_validationPattern_' . $attributeNumber,
                            'wcf.acp.bbcode.attribute.validationPattern.error.invalid'
                        )
                    );
                }
            }
        }
    }
}

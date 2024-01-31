<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field to set the rating of an object.
 *
 * The minimum and maximum rating are handled via `minimum()` and `maximum()`. Fields of this type
 * must have a minimum value and a maximum value. If no value has been set for a field of this class
 * the the field is not nullable, the minimum value will be automatically set when the field's value
 * is requested the first time.
 *
 * This field uses the `wcf.form.field.rating` language item as the default form field label and has
 * a minimum rating of `1` and a maximum rating of `5`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class RatingFormField extends AbstractFormField implements
    IImmutableFormField,
    IMaximumFormField,
    IMinimumFormField,
    INullableFormField
{
    use TDefaultIdFormField;
    use TImmutableFormField;
    use TMaximumFormField {
        maximum as protected traitMaximum;
    }
    use TMinimumFormField {
        minimum as protected traitMinimum;
    }
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_ratingFormField';

    /**
     * Creates a new instance of `RatingFormField`.
     */
    public function __construct()
    {
        $this->label('wcf.form.field.rating');
        $this->minimum(1);
        $this->maximum(5);
    }

    /**
     * Returns the sorted list of possible ratings used to generate the form field's html code.
     *
     * @return  int[]
     */
    public function getRatings()
    {
        if (WCF::getLanguage()->get('wcf.global.pageDirection') === 'rtl') {
            return \range($this->maximum, $this->minimum, -1);
        }

        return \range($this->minimum, $this->maximum);
    }

    /**
     * @inheritDoc
     */
    public function maximum($maximum = null)
    {
        if ($maximum === null) {
            throw new \InvalidArgumentException("Cannot unset maximum value for field '{$this->getId()}'.");
        }

        return $this->traitMaximum($maximum);
    }

    /**
     * @inheritDoc
     */
    public function minimum($minimum = null)
    {
        if ($minimum === null) {
            throw new \InvalidArgumentException("Cannot unset minimum value for field '{$this->getId()}'.");
        }

        return $this->traitMinimum($minimum);
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($this->isNullable() && $value === '') {
                $this->value = null;
            } else {
                $this->value = \intval($value);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'rating';
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->getValue() !== null) {
            if ($this->getValue() < $this->getMinimum() || $this->getValue() > $this->getMaximum()) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalid',
                    'wcf.global.form.error.noValidSelection'
                ));
            }
        }
    }
}

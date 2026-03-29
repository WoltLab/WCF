<?php

namespace wcf\system\form\builder\field;

use Override;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

final class ObjectFilterFormField extends AbstractFormField
{
    protected $templateName = 'shared_objectFilterFormField';

    #[Override]
    public function readValue(): ObjectFilterFormField
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    #[Override]
    public function validate(): void
    {
        // TODO: This error only exists for development purposes!
        $this->addValidationError(
            new FormFieldValidationError('empty')
        );
    }
}

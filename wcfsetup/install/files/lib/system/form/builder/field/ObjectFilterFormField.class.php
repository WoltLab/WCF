<?php

namespace wcf\system\form\builder\field;

use Override;

final class ObjectFilterFormField extends AbstractFormField
{
    protected $templateName = 'shared_objectFilterFormField';

    #[Override]
    public function readValue()
    {
        throw new \Exception('Not implemented');
    }
}

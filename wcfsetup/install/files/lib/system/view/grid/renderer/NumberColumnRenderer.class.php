<?php

namespace wcf\system\view\grid\renderer;

use wcf\util\StringUtil;

class NumberColumnRenderer extends AbstractColumnRenderer
{
    public function render(mixed $value, mixed $context = null): string
    {
        return StringUtil::formatNumeric($value);
    }

    public function getClasses(): string
    {
        return 'columnDigits';
    }
}

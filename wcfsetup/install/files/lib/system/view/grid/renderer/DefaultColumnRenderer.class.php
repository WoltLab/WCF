<?php

namespace wcf\system\view\grid\renderer;

use wcf\util\StringUtil;

class DefaultColumnRenderer extends AbstractColumnRenderer
{
    public function render(mixed $value, mixed $context = null): string
    {
        return StringUtil::encodeHTML($value);
    }

    public function getClasses(): string
    {
        return 'columnText';
    }
}

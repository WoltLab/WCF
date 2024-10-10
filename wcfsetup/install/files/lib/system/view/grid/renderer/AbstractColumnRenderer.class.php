<?php

namespace wcf\system\view\grid\renderer;

abstract class AbstractColumnRenderer implements IColumnRenderer
{
    public function getClasses(): string
    {
        return '';
    }
}

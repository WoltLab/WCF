<?php

namespace wcf\system\view\grid\renderer;

interface IColumnRenderer
{
    public function render(mixed $value, mixed $context = null): string;

    public function getClasses(): string;
}

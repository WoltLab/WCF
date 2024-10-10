<?php

namespace wcf\system\view\grid\action;

use wcf\system\view\grid\AbstractGridView;

interface IGridViewAction
{
    public function render(mixed $row): string;

    public function renderInitialization(AbstractGridView $gridView): ?string;
}

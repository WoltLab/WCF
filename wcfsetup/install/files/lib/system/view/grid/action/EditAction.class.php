<?php

namespace wcf\system\view\grid\action;

use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\view\grid\AbstractGridView;
use wcf\system\WCF;

class EditAction implements IGridViewAction
{
    public function __construct(
        private readonly string $controllerClass,
    ) {}

    #[\Override]
    public function render(mixed $row): string
    {
        \assert($row instanceof DatabaseObject);
        $href = LinkHandler::getInstance()->getControllerLink(
            $this->controllerClass,
            ['object' => $row]
        );

        return '<a href="' . $href . '">' . WCF::getLanguage()->get('wcf.global.button.edit') . '</a>';
    }

    #[\Override]
    public function renderInitialization(AbstractGridView $gridView): ?string
    {
        return null;
    }
}

<?php

namespace wcf\system\view\grid\renderer;

use wcf\data\DatabaseObject;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

class LinkColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(
        private readonly string $controllerClass,
        private readonly array $parameters = [],
        private readonly string $titleLanguageItem = ''
    ) {}


    public function render(mixed $value, mixed $context = null): string
    {
        \assert($context instanceof DatabaseObject);
        $href = LinkHandler::getInstance()->getControllerLink(
            $this->controllerClass,
            \array_merge($this->parameters, ['object' => $context])
        );

        return '<a href="' . $href . '"'
            . ($this->titleLanguageItem ? ' title="' . WCF::getLanguage()->get($this->titleLanguageItem) . '"' : '') . '>'
            . $value
            . '</a>';
    }
}

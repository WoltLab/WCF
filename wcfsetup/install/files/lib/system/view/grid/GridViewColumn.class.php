<?php

namespace wcf\system\view\grid;

use wcf\system\view\grid\renderer\DefaultColumnRenderer;
use wcf\system\view\grid\renderer\IColumnRenderer;
use wcf\system\WCF;

final class GridViewColumn
{
    /**
     * @var IColumnRenderer[]
     */
    private array $renderer = [];
    private string $label = '';
    private static DefaultColumnRenderer $defaultRenderer;
    private bool $sortable = false;

    private function __construct(private readonly string $id) {}

    public static function for(string $id): static
    {
        return new static($id);
    }

    public function render(mixed $value, mixed $context = null): string
    {
        if ($this->getRenderers() === []) {
            return self::getDefaultRenderer()->render($value, $context);
        }

        foreach ($this->getRenderers() as $renderer) {
            $value = $renderer->render($value, $context);
        }

        return $value;
    }

    public function getClasses(): string
    {
        if ($this->getRenderers() === []) {
            return self::getDefaultRenderer()->getClasses();
        }

        return \implode(' ', \array_map(
            static function (IColumnRenderer $renderer) {
                return $renderer->getClasses();
            },
            $this->getRenderers()
        ));
    }

    public function renderer(array|IColumnRenderer $renderers): static
    {
        if (!\is_array($renderers)) {
            $renderers = [$renderers];
        }

        foreach ($renderers as $renderer) {
            \assert($renderer instanceof IColumnRenderer);
            $this->renderer[] = $renderer;
        }

        return $this;
    }

    public function label(string $languageItem): static
    {
        $this->label = WCF::getLanguage()->get($languageItem);

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * @return IColumnRenderer[]
     */
    public function getRenderers(): array
    {
        return $this->renderer;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    private static function getDefaultRenderer(): DefaultColumnRenderer
    {
        if (!isset(self::$defaultRenderer)) {
            self::$defaultRenderer = new DefaultColumnRenderer();
        }

        return self::$defaultRenderer;
    }
}

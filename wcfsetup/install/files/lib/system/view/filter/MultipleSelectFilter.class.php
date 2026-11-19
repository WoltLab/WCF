<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\data\ITitledObject;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\view\filter\exception\InvalidFilterValue;
use wcf\system\WCF;
use wcf\util\ClassUtil;
use wcf\util\HtmlString;

/**
 * Allows a column to be filtered on the basis of multiple selected values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class MultipleSelectFilter extends AbstractFilter
{
    /**
     * `$options` maps the values to their labels. Labels are plain text and
     * encoded by this filter: strings, integers, floats and objects implementing
     * `__toString()` or `ITitledObject` (also decorated ones).
     *
     * If `$labelLanguageItems` is `true`, string labels that are phrase names
     * are resolved as phrases instead, which may contain HTML.
     *
     * @param array<string|int, mixed> $options
     */
    public function __construct(
        protected readonly array $options,
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        protected readonly bool $labelLanguageItems = true
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return MultipleSelectionFormField::create($this->id)
            ->label($this->languageItem)
            ->choices(\array_map($this->getChoiceLabel(...), $this->options));
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $filterValues = $this->unserializeValue($value);
        foreach ($filterValues as $filterValue) {
            if (!isset($this->options[$filterValue])) {
                throw new InvalidFilterValue("Invalid value '{$filterValue}' for filter '{$this->id}' given.");
            }
        }

        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} IN (?)", [$filterValues]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $renderedValues = \array_map(function ($filterValue): string {
            if ($this->isPhraseName($this->options[$filterValue])) {
                return WCF::getLanguage()->get($this->options[$filterValue]);
            } else {
                return $this->getLabelText($this->options[$filterValue]);
            }
        }, $this->unserializeValue($value));

        return \implode(', ', $renderedValues);
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', $value);
    }

    /**
     * @return array<string|int>
     */
    #[\Override]
    public function unserializeValue(string $value): array
    {
        return \explode(',', $value);
    }

    private function getChoiceLabel(mixed $label): HtmlString|string
    {
        if ($this->isPhraseName($label)) {
            return HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable($label));
        }

        return $this->getLabelText($label);
    }

    private function isPhraseName(mixed $label): bool
    {
        return $this->labelLanguageItems
            && \is_string($label)
            && \preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', $label) === 1;
    }

    /**
     * Derives the text of object labels like `ISelectionFormField::choices()`,
     * so that the filter dialog and `renderValue()` show the same text.
     *
     * @throws \InvalidArgumentException if the label is of an unsupported type
     */
    private function getLabelText(mixed $label): string
    {
        if (\is_string($label) || \is_int($label) || \is_float($label)) {
            return (string)$label;
        }

        if (\is_object($label)) {
            if (\method_exists($label, '__toString')) {
                return (string)$label;
            }

            if ($label instanceof ITitledObject || ClassUtil::isDecoratedInstanceOf($label, ITitledObject::class)) {
                return $label->getTitle();
            }
        }

        throw new \InvalidArgumentException(
            "The filter '{$this->id}' has a label of the unsupported type " . \get_debug_type($label) . "."
        );
    }
}

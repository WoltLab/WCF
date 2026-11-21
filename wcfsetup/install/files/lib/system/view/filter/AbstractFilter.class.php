<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Abstract implementation for view filters.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractFilter implements IViewFilter
{
    /**
     * `$languageItem` is the label of this filter, either a phrase name or plain
     * text. It is treated as text, both as the label of the form field in the
     * filter dialog and in the label of an active filter.
     */
    public function __construct(
        protected readonly string $id,
        protected readonly string $languageItem,
        protected readonly string $databaseColumn = ''
    ) {}

    #[\Override]
    public function renderValue(string $value): string
    {
        return $value;
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the label of this filter as plain text, which is the resolved phrase
     * if the label is the name of an existing phrase. Implementations of `getFormField()`
     * set it as the label of the form field with `label(HtmlString::fromText($this->getLabel()))`.
     */
    #[\Override]
    public function getLabel(): string
    {
        return WCF::getLanguage()->get($this->languageItem);
    }

    /**
     * @param DatabaseObjectList<DatabaseObject> $list
     */
    protected function getDatabaseColumnName(DatabaseObjectList $list): string
    {
        return ($this->databaseColumn ?: $list->getDatabaseTableAlias() . '.' . $this->id);
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function unserializeValue(string $value): mixed
    {
        return $value;
    }

    #[\Override]
    public function getFormDataId(): string
    {
        return $this->getId();
    }
}

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
}

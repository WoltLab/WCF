<?php

namespace wcf\system\search;

use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchProvider interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
abstract class AbstractSearchProvider extends AbstractObjectTypeProcessor implements ISearchProvider
{
    #[\Override]
    public function assignVariables(): void
    {
    }

    #[\Override]
    public function getApplication(): string
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }

    #[\Override]
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        return null;
    }

    #[\Override]
    public function getJoins(): string
    {
        return '';
    }

    #[\Override]
    public function getSubjectFieldName(): string
    {
        return $this->getTableName() . '.subject';
    }

    #[\Override]
    public function getUsernameFieldName(): string
    {
        return $this->getTableName() . '.username';
    }

    #[\Override]
    public function getTimeFieldName(): string
    {
        return $this->getTableName() . '.time';
    }

    #[\Override]
    public function getAdditionalData(): ?array
    {
        return null;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return true;
    }

    #[\Override]
    public function getFormTemplateName(): string
    {
        return '';
    }

    #[\Override]
    public function getResultListTemplateName(): string
    {
        return '';
    }

    #[\Override]
    public function getCustomSortField(string $sortField): string
    {
        return '';
    }

    #[\Override]
    public function getFetchObjectsQuery(?PreparedStatementConditionBuilder $additionalConditions = null): string
    {
        return '';
    }

    #[\Override]
    public function getCustomIconName(): ?string
    {
        return null;
    }

    /**
     * @deprecated 5.5
     */
    public function getOuterSQLQuery(
        string $q,
        ?PreparedStatementConditionBuilder &$searchIndexConditions = null,
        ?PreparedStatementConditionBuilder &$additionalConditions = null
    ): string {
        return $this->getFetchObjectsQuery($additionalConditions);
    }
}

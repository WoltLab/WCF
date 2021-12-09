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
 * @package WoltLabSuite\Core\System\Search
 * @since 5.5
 */
abstract class AbstractSearchProvider extends AbstractObjectTypeProcessor implements ISearchProvider
{
    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getApplication(): string
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getJoins(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getSubjectFieldName(): string
    {
        return $this->getTableName() . '.subject';
    }

    /**
     * @inheritDoc
     */
    public function getUsernameFieldName(): string
    {
        return $this->getTableName() . '.username';
    }

    /**
     * @inheritDoc
     */
    public function getTimeFieldName(): string
    {
        return $this->getTableName() . '.time';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplateName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getResultListTemplateName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCustomSortField(string $sortField): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getOuterSQLQuery(
        string $q,
        ?PreparedStatementConditionBuilder &$searchIndexConditions = null,
        ?PreparedStatementConditionBuilder &$additionalConditions = null
    ): string {
        return '';
    }
}

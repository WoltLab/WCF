<?php

namespace wcf\system\search;

use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Interface for full-text search providers.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search
 * @since 5.5
 */
interface ISearchProvider
{
    /**
     * Caches the data for the given object ids.
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void;

    /**
     * Returns the object with the given object id.
     */
    public function getObject(int $objectID): ?ISearchResultObject;

    /**
     * Assigns template variables for displaying the search form.
     */
    public function assignVariables(): void;

    /**
     * Returns the application abbreviation for fetching the provider-specific templates.
     */
    public function getApplication(): string;

    /**
     * Returns the search conditions of this provider or `null` if no special search conditions are necessary.
     * If this provider is the only provider that is searched, the search parameters are passed.
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder;

    /**
     * Provides the ability to add additional joins to the sql search query.
     */
    public function getJoins(): string;

    /**
     * Returns the database table name.
     */
    public function getTableName(): string;

    /**
     * Returns the database field name of the message id.
     */
    public function getIDFieldName(): string;

    /**
     * Returns the database field name of the subject field.
     */
    public function getSubjectFieldName(): string;

    /**
     * Returns the database field name of the username.
     */
    public function getUsernameFieldName(): string;

    /**
     * Returns the database field name of the time.
     */
    public function getTimeFieldName(): string;

    /**
     * Returns additional search information.
     */
    public function getAdditionalData(): ?array;

    /**
     * Returns true if the current user can use this search provider.
     */
    public function isAccessible(): bool;

    /**
     * Returns the name of the form template for this search provider.
     */
    public function getFormTemplateName(): string;

    /**
     * Can optionally return a special template name for displaying the search results.
     */
    public function getResultListTemplateName(): string;

    /**
     * Can optionally return a special sort field for the sql query.
     * This method is only called, if this provider is the only provider that is searched.
     */
    public function getCustomSortField(string $sortField): string;

    /**
     * Replaces the outer SQL query with a custom version. Querying the search index requires the
     * placeholder {WCF_SEARCH_INNER_JOIN} within an empty INNER JOIN() statement.
     */
    public function getOuterSqlQuery(?PreparedStatementConditionBuilder $additionalConditions = null): string;
}

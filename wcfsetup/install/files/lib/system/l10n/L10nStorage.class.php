<?php

namespace wcf\system\l10n;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Reads and writes the localized values of a content type.
 *
 * Values are exchanged as a map of `languageID => value` per column. The key
 * `L10nStorage::MONOLINGUAL` (`0`) represents monolingual content which is
 * stored with `languageID = NULL`. Monolingual and multilingual values are
 * mutually exclusive per column, not per object: different columns may use
 * different language sets, so an object may hold a `languageID = NULL` row for
 * a monolingual column alongside per-language rows for a multilingual column.
 * A column that has no value for a written language id is stored as `NULL` and
 * treated as absent on read.
 *
 * All writes to a `*_l10n` table must go through this class.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nStorage
{
    /**
     * pseudo language id representing the monolingual (`languageID = NULL`) row
     */
    public const MONOLINGUAL = 0;

    public function __construct(
        private readonly L10nDefinition $definition,
    ) {}

    /**
     * Returns the localized values of the given object as a map of
     * `columnName => [languageID => value]` using `MONOLINGUAL` as the key
     * for the monolingual row.
     *
     * @return array<string, array<int, string>>
     */
    public function getValues(int $objectID): array
    {
        return $this->getValuesForObjects([$objectID])[$objectID] ?? [];
    }

    /**
     * Returns the localized values of the given objects as a map of
     * `columnName => [languageID => value]` using `MONOLINGUAL` as the key
     * for the monolingual row.
     *
     * @param non-empty-list<int> $objectIDs
     * @return array<int, array<string, array<int, string>>>
     */
    public function getValuesForObjects(array $objectIDs): array
    {
        $columnList = \implode(', ', $this->definition->columnNames);
        $objectColumnName = $this->definition->objectColumnName;

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add("{$objectColumnName} IN (?)", [$objectIDs]);
        $sql = "SELECT  {$objectColumnName}, languageID, {$columnList}
                FROM    {$this->definition->l10nTableName}
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $values = [];
        while ($row = $statement->fetchArray()) {
            $languageID = $row['languageID'] === null ? self::MONOLINGUAL : (int)$row['languageID'];
            foreach ($this->definition->columnNames as $columnName) {
                $values[$row[$objectColumnName]][$columnName][$languageID] = $row[$columnName];
            }
        }

        return $values;
    }

    /**
     * Replaces the localized values of the given object.
     *
     * Expects a value map for every localized column. Each column may use its
     * own set of language ids; a row is written for every language id that
     * appears in any column and a column that lacks a value for that language
     * id is stored as `NULL`. Combining `MONOLINGUAL` with actual language ids
     * within the same column is invalid.
     *
     * @param array<string, array<int, string>> $values `columnName => [languageID => value]`
     */
    public function setValues(int $objectID, array $values): void
    {
        $languageIDs = $this->validateValues($values);

        $columnList = \implode(', ', $this->definition->columnNames);
        $placeholders = \implode(', ', \array_fill(0, \count($this->definition->columnNames), '?'));

        $sql = "INSERT INTO {$this->definition->l10nTableName}
                            ({$this->definition->objectColumnName}, languageID, {$columnList})
                VALUES      (?, ?, {$placeholders})";
        $insertStatement = WCF::getDB()->prepare($sql);

        $sql = "DELETE FROM {$this->definition->l10nTableName}
                WHERE       {$this->definition->objectColumnName} = ?";
        $deleteStatement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $deleteStatement->execute([$objectID]);

            foreach ($languageIDs as $languageID) {
                $parameters = [
                    $objectID,
                    $languageID === self::MONOLINGUAL ? null : $languageID,
                ];
                foreach ($this->definition->columnNames as $columnName) {
                    $parameters[] = $values[$columnName][$languageID] ?? null;
                }

                $insertStatement->execute($parameters);
            }

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Resolves the effective value from a `languageID => value` map using the
     * deterministic fallback chain: monolingual value, requested language,
     * default language, lowest language id.
     *
     * @param array<int, ?string> $values
     */
    public static function resolveValue(array $values, ?int $languageID = null): string
    {
        if ($values === []) {
            return '';
        }

        if (isset($values[self::MONOLINGUAL])) {
            return $values[self::MONOLINGUAL];
        }

        $languageID ??= WCF::getLanguage()->languageID;
        if (isset($values[$languageID])) {
            return $values[$languageID];
        }

        $defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        if (isset($values[$defaultLanguageID])) {
            return $values[$defaultLanguageID];
        }

        // A column can be `NULL` for a given language, drop those before
        // falling back to the value with the lowest language id.
        $values = \array_filter($values, static fn($value) => $value !== null);
        if ($values === []) {
            return '';
        }

        return $values[\min(\array_keys($values))];
    }

    /**
     * Returns a correlated sub select that resolves the effective value of the
     * given column for use in `SELECT`, `ORDER BY` or `WHERE` clauses. The
     * fallback chain matches `resolveValue()`.
     */
    public function getSubSelect(string $columnName, string $tableAlias, ?int $languageID = null): string
    {
        if (!\in_array($columnName, $this->definition->columnNames, true)) {
            throw new \InvalidArgumentException("Unknown localized column '{$columnName}'.");
        }

        $languageID ??= WCF::getLanguage()->languageID;
        $defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();

        return "(
            SELECT      {$columnName}
            FROM        {$this->definition->l10nTableName}
            WHERE       {$this->definition->objectColumnName} = {$tableAlias}.{$this->definition->objectColumnName}
                    AND {$columnName} IS NOT NULL
            ORDER BY    CASE
                            WHEN languageID IS NULL THEN -3
                            WHEN languageID = {$languageID} THEN -2
                            WHEN languageID = {$defaultLanguageID} THEN -1
                            ELSE languageID
                        END
            LIMIT       1
        )";
    }

    /**
     * Validates the given values and returns the union of language ids across
     * all columns.
     *
     * @param array<string, array<int, string>> $values
     * @return list<int>
     */
    private function validateValues(array $values): array
    {
        $expectedColumns = $this->definition->columnNames;
        $givenColumns = \array_keys($values);
        \sort($expectedColumns);
        \sort($givenColumns);
        if ($expectedColumns !== $givenColumns) {
            throw new \InvalidArgumentException(\sprintf(
                "Expected values for the columns [%s], got [%s].",
                \implode(', ', $this->definition->columnNames),
                \implode(', ', \array_keys($values)),
            ));
        }

        $languageIDs = [];
        foreach ($values as $columnName => $columnValues) {
            if ($columnValues === []) {
                throw new \InvalidArgumentException("Missing values for column '{$columnName}'.");
            }

            $columnLanguageIDs = \array_keys($columnValues);
            if (\in_array(self::MONOLINGUAL, $columnLanguageIDs, true) && \count($columnLanguageIDs) > 1) {
                throw new \InvalidArgumentException(\sprintf(
                    "The monolingual value of column '%s' cannot be combined with language specific values.",
                    $columnName,
                ));
            }

            $languageIDs = [...$languageIDs, ...$columnLanguageIDs];
        }

        $languageIDs = \array_values(\array_unique($languageIDs));
        \sort($languageIDs);

        return $languageIDs;
    }
}

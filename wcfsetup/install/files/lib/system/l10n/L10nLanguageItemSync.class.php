<?php

namespace wcf\system\l10n;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Reusable helpers to keep the localized values of a database object in sync
 * with language variables (`wcf1_language_item`).
 *
 * `migrate()` performs a one-time migration of existing values (literal or
 * phrase based) into an `*_l10n` table and is meant to be called from update
 * scripts. `sync()` refreshes the localized values from the linked language
 * variables on every install or update, preserving values an administrator
 * modified locally (see the `isPristine` handling in `L10nStorage`).
 *
 * Both are usable by any package or plugin that stores localized content in an
 * `*_l10n` table.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nLanguageItemSync
{
    /**
     * Migrates the existing values of a database object into its `*_l10n`
     * table.
     *
     * `$rowMapper` is called for every row of the primary table and returns
     * either `null` (skip the row) or an array with the keys `sources`
     * (`columnName => L10nLanguageItemSource` for every localized column) and
     * the optional `isPristine` flag (defaults to `false`). Language variables
     * of sources flagged with `deleteAfterMigration` are removed afterwards.
     *
     * @param \Closure(array<string, mixed>): (array{sources: array<string, L10nLanguageItemSource>, isPristine?: bool}|null) $rowMapper
     */
    public static function migrate(L10nDefinition $definition, \Closure $rowMapper): void
    {
        $storage = new L10nStorage($definition);
        $installedLanguageIDs = \array_keys(LanguageFactory::getInstance()->getLanguages());
        $defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        $fetchItems = self::createItemReader();

        $sql = "SELECT  *
                FROM    {$definition->primaryTableName}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $obsoleteItems = [];

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            foreach ($rows as $row) {
                $descriptor = $rowMapper($row);
                if ($descriptor === null) {
                    continue;
                }

                $objectID = (int)$row[$definition->objectColumnName];

                $columnItems = [];
                foreach ($definition->columnNames as $columnName) {
                    $source = $descriptor['sources'][$columnName];

                    $items = null;
                    if ($source->languageItem !== null) {
                        $fetched = \array_intersect_key(
                            $fetchItems($source->languageItem),
                            \array_flip($installedLanguageIDs)
                        );
                        if ($fetched !== []) {
                            $items = $fetched;
                            if ($source->deleteAfterMigration) {
                                $obsoleteItems[] = $source->languageItem;
                            }
                        }
                    }

                    $columnItems[$columnName] = $items;
                }

                $values = self::buildValues(
                    $definition,
                    $columnItems,
                    static fn(string $columnName): ?string => $descriptor['sources'][$columnName]->literal,
                    $defaultLanguageID
                );

                $storage->setValues($objectID, $values, $descriptor['isPristine'] ?? false);
            }

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        self::deleteLanguageItems($obsoleteItems);
    }

    /**
     * Refreshes the localized values of every object that is linked to a
     * language variable from the current phrase values. Only pristine rows are
     * updated, see `L10nStorage::syncValues()`.
     */
    public static function sync(L10nDefinition $definition): void
    {
        if (!$definition->supportsLanguageItemSync()) {
            throw new \InvalidArgumentException(
                "The l10n definition of '{$definition->l10nTableName}' does not support the synchronization with language variables."
            );
        }

        $storage = new L10nStorage($definition);
        $installedLanguageIDs = \array_keys(LanguageFactory::getInstance()->getLanguages());
        $defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        $fetchItems = self::createItemReader();

        $sql = "SELECT  {$definition->objectColumnName}, {$definition->identifierColumnName}
                FROM    {$definition->primaryTableName}
                WHERE   {$definition->identifierColumnName} IS NOT NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        while ($row = $statement->fetchArray()) {
            $objectID = (int)$row[$definition->objectColumnName];
            $identifier = $row[$definition->identifierColumnName];

            $columnItems = [];
            $hasItems = false;
            foreach ($definition->columnNames as $columnName) {
                $languageItem = $identifier . $definition->languageItemSuffixes[$columnName];
                $items = \array_intersect_key(
                    $fetchItems($languageItem),
                    \array_flip($installedLanguageIDs)
                );
                $columnItems[$columnName] = $items !== [] ? $items : null;
                $hasItems = $hasItems || $items !== [];
            }

            if (!$hasItems) {
                continue;
            }

            $values = self::buildValues(
                $definition,
                $columnItems,
                static fn(string $columnName): ?string => null,
                $defaultLanguageID
            );

            $storage->syncValues($objectID, $values);
        }
    }

    /**
     * Builds the `columnName => [languageID => value]` map from the resolved
     * language items of each column. Writes a single monolingual row when none
     * of the columns is backed by a language variable.
     *
     * @param array<string, ?array<int, string>> $columnItems
     * @param \Closure(string): ?string $literalProvider
     * @return array<string, array<int, string>>
     */
    private static function buildValues(
        L10nDefinition $definition,
        array $columnItems,
        \Closure $literalProvider,
        int $defaultLanguageID
    ): array {
        $languageIDs = [];
        foreach ($columnItems as $items) {
            if ($items !== null) {
                $languageIDs = [...$languageIDs, ...\array_keys($items)];
            }
        }
        $languageIDs = \array_values(\array_unique($languageIDs));

        $values = [];
        if ($languageIDs === []) {
            foreach ($definition->columnNames as $columnName) {
                $values[$columnName] = [
                    L10nStorage::MONOLINGUAL => $literalProvider($columnName) ?? '',
                ];
            }

            return $values;
        }

        foreach ($languageIDs as $languageID) {
            foreach ($definition->columnNames as $columnName) {
                $values[$columnName][$languageID] = self::resolve(
                    $columnItems[$columnName],
                    $literalProvider($columnName),
                    $languageID,
                    $defaultLanguageID
                ) ?? '';
            }
        }

        return $values;
    }

    /**
     * Resolves the value for a single language mirroring the phrase fallback
     * semantics: requested language, default language, any value, literal.
     *
     * @param ?array<int, string> $items
     */
    private static function resolve(?array $items, ?string $literal, int $languageID, int $defaultLanguageID): ?string
    {
        if ($items === null) {
            return $literal;
        }
        if (\array_key_exists($languageID, $items)) {
            return $items[$languageID];
        }
        if (\array_key_exists($defaultLanguageID, $items)) {
            return $items[$defaultLanguageID];
        }

        return $items !== [] ? \reset($items) : $literal;
    }

    /**
     * Returns a closure that resolves a language variable to its
     * `languageID => value` map.
     *
     * @return \Closure(string): array<int, string>
     */
    private static function createItemReader(): \Closure
    {
        $statement = WCF::getDB()->prepare(
            "SELECT languageID, languageItemValue
             FROM   wcf1_language_item
             WHERE  languageItem = ?"
        );

        return static function (string $languageItem) use ($statement): array {
            $statement->execute([$languageItem]);

            return $statement->fetchMap('languageID', 'languageItemValue');
        };
    }

    /**
     * Removes the given language variables in chunks and resets the language
     * cache.
     *
     * @param list<string> $languageItems
     */
    private static function deleteLanguageItems(array $languageItems): void
    {
        if ($languageItems === []) {
            return;
        }

        foreach (\array_chunk(\array_unique($languageItems), 100) as $chunk) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add('languageItem IN (?)', [$chunk]);

            $sql = "DELETE FROM wcf1_language_item
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
        }

        LanguageFactory::getInstance()->deleteLanguageCache();
    }
}

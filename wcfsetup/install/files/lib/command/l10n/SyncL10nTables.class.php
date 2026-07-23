<?php

namespace wcf\command\l10n;

use wcf\data\package\PackageCache;
use wcf\event\l10n\L10nDefinitionCollecting;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\event\EventHandler;
use wcf\system\l10n\L10nDefinition;
use wcf\system\WCF;

use function wcf\functions\exception\logThrowable;

/**
 * Synchronizes the l10n storage tables with the collected l10n definitions of
 * all database objects. Missing tables are created and missing columns are
 * added, all changes are recorded in the sql log so that the tables are
 * removed when the owning package is uninstalled.
 *
 * The l10n table is always logged under the package that owns the base table
 * of the database object. If a third-party package registers a definition for
 * a database object of another package, its columns become part of that
 * package's table and are not removed when the third-party package is
 * uninstalled.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SyncL10nTables
{
    private const MAX_ITERATIONS = 100;

    public function __invoke(): void
    {
        $definitions = $this->collectDefinitions();
        if ($definitions === []) {
            return;
        }

        $tablesByPackageID = [];
        foreach ($this->groupByTable($definitions) as $tableDefinitions) {
            $baseTableName = $tableDefinitions[0]->getBaseTableName();
            $packageID = $this->getOwningPackageID($baseTableName);
            if ($packageID === null || PackageCache::getInstance()->getPackage($packageID) === null) {
                logThrowable(new \RuntimeException(
                    "Cannot synchronize the l10n table for '{$baseTableName}', the owning package of the base table could not be determined."
                ));

                continue;
            }

            $tablesByPackageID[$packageID][] = $this->buildTable($tableDefinitions);
        }

        foreach ($tablesByPackageID as $packageID => $tables) {
            $package = PackageCache::getInstance()->getPackage($packageID);
            $processor = new DatabaseTableChangeProcessor(
                $package,
                null,
                WCF::getDB()->getEditor(),
            );

            for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
                if (!$processor->process($tables)) {
                    continue 2;
                }
            }

            throw new \RuntimeException(
                "The synchronization of the l10n tables of the package '{$package->package}' did not converge."
            );
        }
    }

    /**
     * @return list<L10nDefinition>
     */
    private function collectDefinitions(): array
    {
        $event = new L10nDefinitionCollecting();
        EventHandler::getInstance()->fire($event);

        return $event->getDefinitions();
    }

    /**
     * Groups the definitions by the name of their l10n table.
     *
     * @param list<L10nDefinition> $definitions
     * @return array<string, non-empty-list<L10nDefinition>>
     */
    private function groupByTable(array $definitions): array
    {
        $groups = [];
        foreach ($definitions as $definition) {
            $groups[$definition->getL10nTableName()][] = $definition;
        }

        return $groups;
    }

    /**
     * Builds the intended layout of the l10n table, merging the payload
     * columns of all given definitions.
     *
     * @param non-empty-list<L10nDefinition> $definitions
     */
    private function buildTable(array $definitions): DatabaseTable
    {
        $baseTableName = $definitions[0]->getBaseTableName();

        $payloadColumns = [];
        foreach ($definitions as $definition) {
            foreach ($definition->columns as $column) {
                $name = \strtolower($column->getName());
                if (isset($payloadColumns[$name])) {
                    throw new \LogicException(
                        "The column '{$column->getName()}' of the l10n table '{$definitions[0]->getL10nTableName()}' has been defined multiple times."
                    );
                }

                $payloadColumns[$name] = $column;
            }
        }

        return DatabaseTable::create($definitions[0]->getL10nTableName())
            ->columns([
                NotNullInt10DatabaseTableColumn::create('objectID'),
                IntDatabaseTableColumn::create('languageID')->length(10),
                ...\array_values($payloadColumns),
            ])
            ->indices([
                DatabaseTableIndex::create('')
                    ->columns(['objectID', 'languageID']),
            ])
            ->foreignKeys([
                DatabaseTableForeignKey::create()
                    ->columns(['objectID'])
                    ->referencedTable($baseTableName)
                    ->referencedColumns([$definitions[0]->getBaseTableIndexName()])
                    ->onDelete('CASCADE'),
                DatabaseTableForeignKey::create()
                    ->columns(['languageID'])
                    ->referencedTable('wcf1_language')
                    ->referencedColumns(['languageID'])
                    ->onDelete('CASCADE'),
            ]);
    }

    /**
     * Returns the id of the package that owns the given base table or `null`
     * if the table is not recorded in the sql log.
     */
    private function getOwningPackageID(string $baseTableName): ?int
    {
        $sql = "SELECT  packageID
                FROM    wcf1_package_installation_sql_log
                WHERE   sqlTable = ?
                    AND sqlColumn = ''
                    AND sqlIndex = ''
                    AND isDone = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $baseTableName,
            1,
        ]);

        $packageID = $statement->fetchSingleColumn();

        return $packageID === false ? null : (int)$packageID;
    }
}

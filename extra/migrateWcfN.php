#!/usr/bin/env php
<?php
// @codingStandardsIgnoreFile

/**
 * Migrates an installation that uses a `WCF_N` value other than `1` to `WCF_N = 1`.
 *
 * Renames all `appN_*` tables to `app1_*`, rewrites the table names in
 * `wcf1_package_installation_sql_log`, renames the generated foreign key
 * constraints (`md5(tableName_firstColumn)_fk`) and indices
 * (`md5(tableName_firstColumn)`) so that they match the names the package
 * installation plugins compute for the new table names, sets `WCF_N` to `1` in `config.inc.php` and finally clears the cache.
 *
 * Every step is idempotent, the script can be aborted at any point and simply
 * be started again. The `WCF_N` value in `config.inc.php` is only rewritten
 * once all other steps have been verified.
 *
 * Usage: php extra/migrateWcfN.php /path/to/wcf/config.inc.php [--yes] [--dry-run]
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

if (\PHP_SAPI !== 'cli') {
    exit;
}

const SQL_LOG_TABLE = 'package_installation_sql_log';

const FOREIGN_KEY_RULES = ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION', 'SET DEFAULT'];

function out(string $message): void
{
    \fwrite(\STDOUT, $message . "\n");
}

function fail(string $message): never
{
    throw new \RuntimeException($message);
}

/**
 * @param string[] $argv
 * @return array{configPath: string, yes: bool, dryRun: bool}
 */
function parseArguments(array $argv): array
{
    $configPath = null;
    $yes = false;
    $dryRun = false;

    foreach (\array_slice($argv, 1) as $argument) {
        if ($argument === '--yes') {
            $yes = true;
        } elseif ($argument === '--dry-run') {
            $dryRun = true;
        } elseif (\str_starts_with($argument, '--')) {
            fail("Unknown option '{$argument}'.");
        } elseif ($configPath === null) {
            $configPath = $argument;
        } else {
            fail("Unexpected argument '{$argument}'.");
        }
    }

    if ($configPath === null) {
        fail("Usage: php {$argv[0]} /path/to/wcf/config.inc.php [--yes] [--dry-run]");
    }

    return [
        'configPath' => $configPath,
        'yes' => $yes,
        'dryRun' => $dryRun,
    ];
}

/**
 * @return array{host: string, port: int, user: string, password: string, database: string, driverOptions: array<int, mixed>, wcfN: int}
 */
function loadConfig(string $configPath): array
{
    if (!\is_file($configPath)) {
        fail("The file '{$configPath}' does not exist.");
    }
    if (!\is_readable($configPath)) {
        fail("The file '{$configPath}' is not readable.");
    }
    if (!\is_writable($configPath)) {
        fail("The file '{$configPath}' is not writable, but it must be rewritten in the final step.");
    }
    $cacheDirectory = getCacheDirectory($configPath);
    if (\is_dir($cacheDirectory) && !\is_writable($cacheDirectory)) {
        fail("The directory '{$cacheDirectory}' is not writable, but the cache must be cleared in the final step.");
    }

    // Mirrors `WCF::initDB()`, the config is a plain PHP file assigning these variables.
    $config = (static function (string $path): array {
        require $path;
        $variables = \get_defined_vars();

        return [
            'host' => (string)($variables['dbHost'] ?? ''),
            'port' => (int)($variables['dbPort'] ?? 0),
            'user' => (string)($variables['dbUser'] ?? ''),
            'password' => (string)($variables['dbPassword'] ?? ''),
            'database' => (string)($variables['dbName'] ?? ''),
            'driverOptions' => $variables['defaultDriverOptions'] ?? [],
        ];
    })($configPath);

    if (!\defined('WCF_N')) {
        fail("The file '{$configPath}' does not define 'WCF_N'.");
    }
    if (!\is_array($config['driverOptions'])) {
        fail("The file '{$configPath}' assigns a non-array value to '\$defaultDriverOptions'.");
    }
    if ($config['host'] === '' || $config['database'] === '') {
        fail("The file '{$configPath}' does not contain a database configuration.");
    }

    $config['wcfN'] = (int)\constant('WCF_N');

    return $config;
}

/**
 * @param array{host: string, port: int, user: string, password: string, database: string, driverOptions: array<int, mixed>, wcfN: int} $config
 */
function connect(array $config): \PDO
{
    if (!\extension_loaded('pdo_mysql')) {
        fail("The 'pdo_mysql' extension is not available.");
    }

    $port = $config['port'] > 0 ? $config['port'] : 3306;
    $dsn = "mysql:host={$config['host']};port={$port};dbname={$config['database']}";

    // Like `MySQLDatabase::connect()`, the options the script relies on take precedence over the configured ones.
    $driverOptions = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ] + $config['driverOptions'];
    if (\defined('\\Pdo\\Mysql::ATTR_INIT_COMMAND')) {
        $driverOptions[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES 'utf8mb4'";
    } else {
        $driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8mb4'";
    }

    try {
        return new \PDO($dsn, $config['user'], $config['password'], $driverOptions);
    } catch (\PDOException $e) {
        if ($config['password'] === '' && \in_array($e->errorInfo[1] ?? null, [1045, 1698], true)) {
            fail(
                $e->getMessage() . "\n"
                    . "The account has no password and probably uses socket authentication, which checks the OS user running this script ('" . getOsUserName() . "').\n"
                    . "Run the script as the user of the PHP-FPM pool, e.g. 'sudo -u <pool-user> php {$_SERVER['argv'][0]} …'."
            );
        }

        throw $e;
    }
}

function getOsUserName(): string
{
    if (!\function_exists('posix_geteuid')) {
        return 'unknown';
    }

    $uid = \posix_geteuid();
    $entry = \posix_getpwuid($uid);

    return $entry !== false ? $entry['name'] : (string)$uid;
}

function checkServerVersion(\PDO $pdo): void
{
    $version = (string)$pdo->query("SELECT VERSION()")->fetchColumn();
    if (\preg_match('~^(\d+\.\d+\.\d+)~', $version, $matches) !== 1) {
        fail("Unable to parse the database server version '{$version}'.");
    }

    // Same requirements as `WCFSetup`, both support `RENAME INDEX` and in-place foreign key changes.
    if (\stripos($version, 'MariaDB') !== false) {
        if (\version_compare($matches[1], '10.5.15') < 0) {
            fail("Insufficient MariaDB version '{$version}', '10.5.15' or greater is required.");
        }
    } elseif (\version_compare($matches[1], '8.0.30') < 0) {
        fail("Insufficient MySQL version '{$version}', '8.0.30' or greater is required.");
    }
}

function quoteIdentifier(string $identifier): string
{
    if (\preg_match('~^[A-Za-z0-9_]+$~', $identifier) !== 1) {
        fail("Refusing to use the unexpected identifier '{$identifier}'.");
    }

    return "`{$identifier}`";
}

/**
 * @return array<string, string> table name => table type
 */
function listTables(\PDO $pdo, string $schema): array
{
    $statement = $pdo->prepare(
        "SELECT  TABLE_NAME, TABLE_TYPE
         FROM    information_schema.TABLES
         WHERE   TABLE_SCHEMA = ?"
    );
    $statement->execute([$schema]);

    $tables = [];
    foreach ($statement->fetchAll() as $row) {
        $tables[(string)$row['TABLE_NAME']] = (string)$row['TABLE_TYPE'];
    }

    return $tables;
}

/**
 * Resolves the abbreviations of all installed applications, see `Package::getAbbreviation()`.
 *
 * @param array<string, string> $tables
 * @return string[]
 */
function getApplicationAbbreviations(\PDO $pdo, array $tables, int $wcfN): array
{
    $packageTable = null;
    foreach (["wcf{$wcfN}_package", 'wcf1_package'] as $candidate) {
        if (isset($tables[$candidate])) {
            $packageTable = $candidate;
            break;
        }
    }
    if ($packageTable === null) {
        fail("Neither 'wcf{$wcfN}_package' nor 'wcf1_package' exists, this does not look like a WoltLab Suite database.");
    }

    $statement = $pdo->prepare(
        "SELECT  package
         FROM    " . quoteIdentifier($packageTable) . "
         WHERE   isApplication = 1"
    );
    $statement->execute();

    $abbreviations = ['wcf'];
    foreach ($statement->fetchAll(\PDO::FETCH_COLUMN) as $package) {
        $parts = \explode('.', (string)$package);
        $abbreviation = \array_pop($parts);
        if (!\in_array($abbreviation, $abbreviations, true)) {
            $abbreviations[] = $abbreviation;
        }
    }

    return $abbreviations;
}

/**
 * Maps table names between the old and the new prefix.
 */
final class TableNames
{
    private readonly string $oldRegex;

    private readonly string $newRegex;

    /**
     * @param string[] $abbreviations
     */
    public function __construct(array $abbreviations, private readonly int $wcfN)
    {
        $alternatives = \implode('|', \array_map(static fn(string $abbreviation) => \preg_quote($abbreviation, '~'), $abbreviations));
        $this->oldRegex = "~^({$alternatives}){$wcfN}_(.+)$~";
        $this->newRegex = "~^({$alternatives})1_(.+)$~";
    }

    public function isOld(string $tableName): bool
    {
        return \preg_match($this->oldRegex, $tableName) === 1;
    }

    public function isNew(string $tableName): bool
    {
        return \preg_match($this->newRegex, $tableName) === 1;
    }

    public function toNew(string $tableName): string
    {
        if (\preg_match($this->oldRegex, $tableName, $matches) !== 1) {
            fail("'{$tableName}' does not carry the old table prefix.");
        }

        return "{$matches[1]}1_{$matches[2]}";
    }

    public function toOld(string $tableName): string
    {
        if (\preg_match($this->newRegex, $tableName, $matches) !== 1) {
            fail("'{$tableName}' does not carry the new table prefix.");
        }

        return "{$matches[1]}{$this->wcfN}_{$matches[2]}";
    }

    /**
     * Returns the old and the new name for a table that may currently use either prefix.
     *
     * @return array{old: string, new: string}
     */
    public function resolve(string $currentName): array
    {
        if ($this->isOld($currentName)) {
            return ['old' => $currentName, 'new' => $this->toNew($currentName)];
        }

        return ['old' => $this->toOld($currentName), 'new' => $currentName];
    }
}

/**
 * @param array<string, string> $tables
 * @return array<string, string> old name => new name
 */
function collectTableRenames(array $tables, TableNames $names): array
{
    $renames = [];
    $views = [];
    $conflicts = [];
    foreach ($tables as $tableName => $tableType) {
        if (!$names->isOld($tableName)) {
            continue;
        }

        if ($tableType !== 'BASE TABLE') {
            $views[] = $tableName;
            continue;
        }

        $newName = $names->toNew($tableName);
        if (isset($tables[$newName])) {
            $conflicts[] = "{$tableName} -> {$newName}";
            continue;
        }

        $renames[$tableName] = $newName;
    }

    if ($views !== []) {
        fail("Refusing to rename non-table objects: " . \implode(', ', $views));
    }
    if ($conflicts !== []) {
        fail(
            "The target table already exists for: " . \implode(', ', $conflicts) . "\n"
                . "This usually means that another installation using 'WCF_N = 1' shares this database. Nothing was changed."
        );
    }

    return $renames;
}

/**
 * @param array<string, string> $renames
 */
function renameTables(\PDO $pdo, array $renames): void
{
    if ($renames === []) {
        out("  No tables left to rename.");

        return;
    }

    $pairs = [];
    foreach ($renames as $oldName => $newName) {
        $pairs[] = quoteIdentifier($oldName) . " TO " . quoteIdentifier($newName);
    }

    // A single statement is atomic and MySQL takes care of the ordering between referencing tables.
    $pdo->exec("RENAME TABLE " . \implode(', ', $pairs));

    out("  Renamed " . \count($renames) . " tables.");
}

/**
 * @return string[]
 */
function getSqlLogTableNames(\PDO $pdo, string $logTable, TableNames $names): array
{
    $statement = $pdo->prepare(
        "SELECT  DISTINCT sqlTable
         FROM    " . quoteIdentifier($logTable)
    );
    $statement->execute();

    return \array_values(\array_filter(
        $statement->fetchAll(\PDO::FETCH_COLUMN),
        static fn(string $sqlTable) => $names->isOld($sqlTable)
    ));
}

function rewriteSqlLogTableNames(\PDO $pdo, string $logTable, TableNames $names): void
{
    $sqlTables = getSqlLogTableNames($pdo, $logTable, $names);
    if ($sqlTables === []) {
        out("  No log entries left to rewrite.");

        return;
    }

    $log = quoteIdentifier($logTable);

    // Drop entries that already exist under the new name, this happens for
    // search index tables that were logged with the unreplaced `wcf1_` prefix.
    $deleteDuplicates = $pdo->prepare(
        "DELETE  o
         FROM    {$log} o
         JOIN    {$log} n
         ON      n.packageID = o.packageID
             AND n.sqlTable = ?
             AND n.sqlColumn = o.sqlColumn
             AND n.sqlIndex = o.sqlIndex
         WHERE   o.sqlTable = ?"
    );
    $updateTable = $pdo->prepare(
        "UPDATE  {$log}
         SET     sqlTable = ?
         WHERE   sqlTable = ?"
    );
    $selectIndices = $pdo->prepare(
        "SELECT  DISTINCT sqlIndex
         FROM    {$log}
         WHERE   sqlTable = ?
             AND sqlIndex <> ''"
    );
    $updateIndex = $pdo->prepare(
        "UPDATE  {$log}
         SET     sqlIndex = ?
         WHERE   sqlTable = ?
             AND sqlIndex = ?"
    );

    $rows = 0;
    foreach ($sqlTables as $oldName) {
        $newName = $names->toNew($oldName);

        // A re-run no longer finds the table once `sqlTable` carries the new name,
        // the `_ibfk_` rewrite must therefore never be left behind on its own.
        $pdo->beginTransaction();
        try {
            $deleteDuplicates->execute([$newName, $oldName]);
            $updateTable->execute([$newName, $oldName]);
            $rows += $updateTable->rowCount();

            // MySQL renames auto-generated `table_ibfk_N` constraints together with the table.
            $selectIndices->execute([$newName]);
            foreach ($selectIndices->fetchAll(\PDO::FETCH_COLUMN) as $sqlIndex) {
                $sqlIndex = (string)$sqlIndex;
                if (\str_starts_with($sqlIndex, "{$oldName}_ibfk_")) {
                    $updateIndex->execute([
                        $newName . \substr($sqlIndex, \strlen($oldName)),
                        $newName,
                        $sqlIndex,
                    ]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    out("  Rewrote {$rows} log entries for " . \count($sqlTables) . " tables.");
}

/**
 * Returns all foreign keys defined on tables that belong to this installation.
 *
 * @return list<array{table: string, name: string, columns: string[], referencedTable: string, referencedColumns: string[], onDelete: string, onUpdate: string}>
 */
function collectForeignKeys(\PDO $pdo, string $schema, TableNames $names): array
{
    $statement = $pdo->prepare(
        "SELECT     kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.ORDINAL_POSITION,
                    kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME,
                    rc.DELETE_RULE, rc.UPDATE_RULE
         FROM       information_schema.KEY_COLUMN_USAGE kcu
         JOIN       information_schema.REFERENTIAL_CONSTRAINTS rc
         ON         rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.TABLE_NAME = kcu.TABLE_NAME
         WHERE      kcu.TABLE_SCHEMA = ?
                AND kcu.CONSTRAINT_SCHEMA = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
         ORDER BY   kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION"
    );
    $statement->execute([$schema, $schema]);

    $foreignKeys = [];
    foreach ($statement->fetchAll() as $row) {
        $tableName = (string)$row['TABLE_NAME'];
        if (!$names->isOld($tableName) && !$names->isNew($tableName)) {
            continue;
        }

        $key = "{$tableName}.{$row['CONSTRAINT_NAME']}";
        if (!isset($foreignKeys[$key])) {
            $foreignKeys[$key] = [
                'table' => $tableName,
                'name' => (string)$row['CONSTRAINT_NAME'],
                'columns' => [],
                'referencedTable' => (string)$row['REFERENCED_TABLE_NAME'],
                'referencedColumns' => [],
                'onDelete' => \strtoupper((string)$row['DELETE_RULE']),
                'onUpdate' => \strtoupper((string)$row['UPDATE_RULE']),
            ];
        }

        $foreignKeys[$key]['columns'][] = (string)$row['COLUMN_NAME'];
        $foreignKeys[$key]['referencedColumns'][] = (string)$row['REFERENCED_COLUMN_NAME'];
    }

    return \array_values($foreignKeys);
}

/**
 * Computes the generated index names for the old and the new table name,
 * see `DatabaseTable::indices()` and `SQLParser::getGenericIndexName()`.
 *
 * @return array{old: string, new: string}
 */
function getGeneratedIndexNames(string $tableName, string $firstColumn, TableNames $names): array
{
    $tableNames = $names->resolve($tableName);

    return [
        'old' => \md5("{$tableNames['old']}_{$firstColumn}"),
        'new' => \md5("{$tableNames['new']}_{$firstColumn}"),
    ];
}

/**
 * Computes the generated constraint names for the old and the new table name,
 * see `DatabaseTable::foreignKeys()` and `SQLParser::getGenericIndexName()`.
 *
 * @param array{table: string, columns: string[]} $foreignKey
 * @return array{old: string, new: string}
 */
function getGeneratedForeignKeyNames(array $foreignKey, TableNames $names): array
{
    $indexNames = getGeneratedIndexNames($foreignKey['table'], $foreignKey['columns'][0], $names);

    return [
        'old' => $indexNames['old'] . '_fk',
        'new' => $indexNames['new'] . '_fk',
    ];
}

/**
 * @return string[]
 */
function getIndexNames(\PDO $pdo, string $schema, string $tableName): array
{
    $statement = $pdo->prepare(
        "SELECT  DISTINCT INDEX_NAME
         FROM    information_schema.STATISTICS
         WHERE   TABLE_SCHEMA = ?
             AND TABLE_NAME = ?"
    );
    $statement->execute([$schema, $tableName]);

    return \array_map('strval', $statement->fetchAll(\PDO::FETCH_COLUMN));
}

/**
 * Returns the foreign keys whose constraint still carries the name generated from the old table name.
 *
 * @param list<array{table: string, name: string, columns: string[]}> $foreignKeys
 * @return list<array{table: string, name: string, columns: string[]}>
 */
function collectPendingForeignKeyRenames(array $foreignKeys, TableNames $names): array
{
    return \array_values(\array_filter(
        $foreignKeys,
        static fn(array $foreignKey) => $foreignKey['name'] === getGeneratedForeignKeyNames($foreignKey, $names)['old']
    ));
}

function renameForeignKeys(\PDO $pdo, string $schema, string $logTable, TableNames $names): void
{
    $foreignKeys = collectForeignKeys($pdo, $schema, $names);

    $updateLog = $pdo->prepare(
        "UPDATE  " . quoteIdentifier($logTable) . "
         SET     sqlIndex = ?
         WHERE   sqlTable = ?
             AND sqlIndex = ?"
    );

    $renamedConstraints = 0;
    $renamedIndices = 0;
    $rewrittenLogEntries = 0;

    // Adding a foreign key is only an in-place operation without the checks, the data is known to be valid.
    $pdo->exec("SET SESSION foreign_key_checks = 0");
    try {
        foreach ($foreignKeys as $foreignKey) {
            if ($names->isOld($foreignKey['table'])) {
                fail("Table '{$foreignKey['table']}' still carries the old prefix, the rename step did not complete.");
            }

            $generatedNames = getGeneratedForeignKeyNames($foreignKey, $names);
            $table = quoteIdentifier($foreignKey['table']);

            if ($foreignKey['name'] === $generatedNames['old']) {
                if (!\in_array($foreignKey['onDelete'], FOREIGN_KEY_RULES, true)) {
                    fail("Unexpected ON DELETE rule '{$foreignKey['onDelete']}' for '{$foreignKey['table']}.{$foreignKey['name']}'.");
                }
                if (!\in_array($foreignKey['onUpdate'], FOREIGN_KEY_RULES, true)) {
                    fail("Unexpected ON UPDATE rule '{$foreignKey['onUpdate']}' for '{$foreignKey['table']}.{$foreignKey['name']}'.");
                }

                // Dropping and re-adding within one statement is all-or-nothing,
                // the definition is never lost if the script is aborted.
                $pdo->exec(
                    "ALTER TABLE {$table}
                     DROP FOREIGN KEY " . quoteIdentifier($generatedNames['old']) . ",
                     ADD CONSTRAINT " . quoteIdentifier($generatedNames['new']) . "
                         FOREIGN KEY (" . \implode(', ', \array_map('quoteIdentifier', $foreignKey['columns'])) . ")
                         REFERENCES " . quoteIdentifier($foreignKey['referencedTable']) . "
                         (" . \implode(', ', \array_map('quoteIdentifier', $foreignKey['referencedColumns'])) . ")
                         ON DELETE {$foreignKey['onDelete']}
                         ON UPDATE {$foreignKey['onUpdate']},
                     ALGORITHM = INPLACE"
                );
                $renamedConstraints++;
            }

            $indexNames = getIndexNames($pdo, $schema, $foreignKey['table']);
            if (
                \in_array($generatedNames['old'], $indexNames, true)
                && !\in_array($generatedNames['new'], $indexNames, true)
            ) {
                $pdo->exec(
                    "ALTER TABLE {$table}
                     RENAME INDEX " . quoteIdentifier($generatedNames['old']) . " TO " . quoteIdentifier($generatedNames['new']) . ",
                     ALGORITHM = INPLACE"
                );
                $renamedIndices++;
            }

            $updateLog->execute([$generatedNames['new'], $foreignKey['table'], $generatedNames['old']]);
            $rewrittenLogEntries += $updateLog->rowCount();
        }
    } finally {
        $pdo->exec("SET SESSION foreign_key_checks = 1");
    }

    out("  Renamed {$renamedConstraints} constraints, {$renamedIndices} supporting indices and {$rewrittenLogEntries} log entries.");
}

/**
 * Returns all indices except the primary key defined on tables that belong to this installation.
 *
 * @return list<array{table: string, name: string, firstColumn: string}>
 */
function collectIndices(\PDO $pdo, string $schema, TableNames $names): array
{
    $statement = $pdo->prepare(
        "SELECT     TABLE_NAME, INDEX_NAME, COLUMN_NAME
         FROM       information_schema.STATISTICS
         WHERE      TABLE_SCHEMA = ?
                AND INDEX_NAME <> 'PRIMARY'
                AND SEQ_IN_INDEX = 1
         ORDER BY   TABLE_NAME, INDEX_NAME"
    );
    $statement->execute([$schema]);

    $indices = [];
    foreach ($statement->fetchAll() as $row) {
        $tableName = (string)$row['TABLE_NAME'];
        if (!$names->isOld($tableName) && !$names->isNew($tableName)) {
            continue;
        }

        $indices[] = [
            'table' => $tableName,
            'name' => (string)$row['INDEX_NAME'],
            'firstColumn' => (string)$row['COLUMN_NAME'],
        ];
    }

    return $indices;
}

/**
 * Returns the indices that still carry the name generated from the old table name.
 *
 * @param list<array{table: string, name: string, firstColumn: string}> $indices
 * @return list<array{table: string, name: string, firstColumn: string}>
 */
function collectPendingIndexRenames(array $indices, TableNames $names): array
{
    return \array_values(\array_filter(
        $indices,
        static fn(array $index) => $index['name'] === getGeneratedIndexNames($index['table'], $index['firstColumn'], $names)['old']
    ));
}

/**
 * Renames the indices whose name was generated from the table name, the
 * supporting indices of foreign keys are handled by `renameForeignKeys()`.
 */
function renameIndices(\PDO $pdo, string $schema, string $logTable, TableNames $names): void
{
    $indices = collectIndices($pdo, $schema, $names);

    $existingNames = [];
    foreach ($indices as $index) {
        $existingNames[$index['table']][] = $index['name'];
    }

    $updateLog = $pdo->prepare(
        "UPDATE  " . quoteIdentifier($logTable) . "
         SET     sqlIndex = ?
         WHERE   sqlTable = ?
             AND sqlIndex = ?"
    );

    $renamedIndices = 0;
    $rewrittenLogEntries = 0;

    foreach ($indices as $index) {
        if ($names->isOld($index['table'])) {
            fail("Table '{$index['table']}' still carries the old prefix, the rename step did not complete.");
        }

        $generatedNames = getGeneratedIndexNames($index['table'], $index['firstColumn'], $names);

        if ($index['name'] === $generatedNames['old']) {
            if (\in_array($generatedNames['new'], $existingNames[$index['table']], true)) {
                fail("Cannot rename index '{$index['table']}.{$index['name']}', '{$generatedNames['new']}' already exists.");
            }

            $pdo->exec(
                "ALTER TABLE " . quoteIdentifier($index['table']) . "
                 RENAME INDEX " . quoteIdentifier($generatedNames['old']) . " TO " . quoteIdentifier($generatedNames['new']) . ",
                 ALGORITHM = INPLACE"
            );
            $renamedIndices++;
        } elseif ($index['name'] !== $generatedNames['new']) {
            continue;
        }

        // DDL commits implicitly, a re-run must still rewrite the log for an index renamed before an abort.
        $updateLog->execute([$generatedNames['new'], $index['table'], $generatedNames['old']]);
        $rewrittenLogEntries += $updateLog->rowCount();
    }

    out("  Renamed {$renamedIndices} indices and {$rewrittenLogEntries} log entries.");
}

function verifyMigration(\PDO $pdo, string $schema, string $logTable, TableNames $names): void
{
    $errors = [];

    foreach (\array_keys(listTables($pdo, $schema)) as $tableName) {
        if ($names->isOld($tableName)) {
            $errors[] = "Table '{$tableName}' still carries the old prefix.";
        }
    }

    foreach (collectForeignKeys($pdo, $schema, $names) as $foreignKey) {
        if ($foreignKey['name'] === getGeneratedForeignKeyNames($foreignKey, $names)['old']) {
            $errors[] = "Foreign key '{$foreignKey['table']}.{$foreignKey['name']}' still carries the old generated name.";
        }
    }

    foreach (collectPendingIndexRenames(collectIndices($pdo, $schema, $names), $names) as $index) {
        $errors[] = "Index '{$index['table']}.{$index['name']}' still carries the old generated name.";
    }

    foreach (getSqlLogTableNames($pdo, $logTable, $names) as $sqlTable) {
        $errors[] = "Log entries for '{$sqlTable}' still carry the old prefix.";
    }

    if ($errors !== []) {
        fail("Verification failed, 'config.inc.php' was not modified:\n  " . \implode("\n  ", $errors));
    }
}

function rewriteConfig(string $configPath): void
{
    $content = \file_get_contents($configPath);
    if ($content === false) {
        fail("Unable to read '{$configPath}'.");
    }

    $newContent = \preg_replace(
        '~define\(\s*([\'"])WCF_N\1\s*,\s*\d+\s*\)~',
        "define('WCF_N', 1)",
        $content,
        -1,
        $count
    );
    if ($newContent === null || $count !== 1) {
        fail("Expected exactly one 'WCF_N' definition in '{$configPath}', found {$count}.");
    }

    // Written in place to preserve the owner and the permissions of the file.
    if (\file_put_contents($configPath, $newContent, \LOCK_EX) !== \strlen($newContent)) {
        fail("Unable to write '{$configPath}'.");
    }
    if (\file_get_contents($configPath) !== $newContent) {
        fail("Verification of the rewritten '{$configPath}' failed.");
    }
}

function getCacheDirectory(string $configPath): string
{
    return \dirname($configPath) . '/cache/';
}

/**
 * Mirrors `DiskCacheSource::flushAll()`.
 */
function clearCache(string $configPath): void
{
    $cacheDirectory = getCacheDirectory($configPath);
    if (!\is_dir($cacheDirectory)) {
        out("  The cache directory '{$cacheDirectory}' does not exist.");

        return;
    }

    $deleted = 0;
    $failed = [];
    foreach (new \DirectoryIterator($cacheDirectory) as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        if (@\unlink($file->getPathname())) {
            $deleted++;
        } else {
            $failed[] = $file->getFilename();
        }
    }

    // `WCF_N` is already rewritten at this point, a re-run would exit early and never reach this step.
    if ($failed !== []) {
        fail("Unable to delete these files in '{$cacheDirectory}', delete them manually:\n  " . \implode("\n  ", $failed));
    }

    out("  Deleted {$deleted} cache files.");
}

function confirm(): void
{
    \fwrite(\STDOUT, "Type 'yes' to continue: ");
    $answer = \fgets(\STDIN);
    if ($answer === false || \trim($answer) !== 'yes') {
        fail("Aborted, nothing was changed.");
    }
}

/**
 * @param string[] $argv
 */
function main(array $argv): int
{
    $arguments = parseArguments($argv);
    $config = loadConfig($arguments['configPath']);

    if ($config['wcfN'] === 1) {
        out("'WCF_N' is already '1', nothing to do.");

        return 0;
    }
    if ($config['wcfN'] < 1) {
        fail("Unexpected 'WCF_N' value '{$config['wcfN']}'.");
    }

    $pdo = connect($config);
    checkServerVersion($pdo);

    $schema = $config['database'];
    $tables = listTables($pdo, $schema);
    $names = new TableNames(getApplicationAbbreviations($pdo, $tables, $config['wcfN']), $config['wcfN']);

    $renames = collectTableRenames($tables, $names);

    $oldLogTable = "wcf{$config['wcfN']}_" . SQL_LOG_TABLE;
    $newLogTable = 'wcf1_' . SQL_LOG_TABLE;
    if (!isset($tables[$oldLogTable]) && !isset($tables[$newLogTable])) {
        fail("Neither '{$oldLogTable}' nor '{$newLogTable}' exists.");
    }
    $pendingLogTables = getSqlLogTableNames($pdo, isset($tables[$oldLogTable]) ? $oldLogTable : $newLogTable, $names);
    $pendingForeignKeys = collectPendingForeignKeyRenames(collectForeignKeys($pdo, $schema, $names), $names);
    $pendingIndices = collectPendingIndexRenames(collectIndices($pdo, $schema, $names), $names);

    out("Migrating 'WCF_N = {$config['wcfN']}' to 'WCF_N = 1' in database '{$schema}' on '{$config['host']}'.");
    out("  Tables to rename:                " . \count($renames));
    out("  Log entries with tables to fix:  " . \count($pendingLogTables));
    out("  Foreign keys to rename:          " . \count($pendingForeignKeys));
    out("  Indices to rename:               " . \count($pendingIndices));
    out("");
    out("Make sure that a backup exists and that the installation is in maintenance mode.");

    if ($arguments['dryRun']) {
        out("Dry run, nothing was changed.");

        return 0;
    }
    if (!$arguments['yes']) {
        confirm();
    }

    out("Step 1/6: Renaming tables");
    renameTables($pdo, $renames);

    if (!isset(listTables($pdo, $schema)[$newLogTable])) {
        fail("'{$newLogTable}' does not exist after renaming the tables.");
    }

    out("Step 2/6: Rewriting table names in the SQL log");
    rewriteSqlLogTableNames($pdo, $newLogTable, $names);

    out("Step 3/6: Renaming generated foreign key constraints");
    renameForeignKeys($pdo, $schema, $newLogTable, $names);

    out("Step 4/6: Renaming generated indices");
    renameIndices($pdo, $schema, $newLogTable, $names);

    out("Step 5/6: Verifying and updating 'config.inc.php'");
    verifyMigration($pdo, $schema, $newLogTable, $names);
    rewriteConfig($arguments['configPath']);

    out("Step 6/6: Clearing the cache");
    clearCache($arguments['configPath']);

    out("");
    out("Done. 'WCF_N' is now '1'.");
    out("Disable the maintenance mode.");

    return 0;
}

try {
    exit(main($_SERVER['argv']));
} catch (\Throwable $e) {
    \fwrite(\STDERR, "Error: " . $e->getMessage() . "\n");

    exit(1);
}

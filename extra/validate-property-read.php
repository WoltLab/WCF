#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Validates that @property-read annotations in DatabaseObject classes
 * match the database schema defined in the install file.
 *
 * Usage: php validate-property-read.php [<package-directory>]
 *
 * When no argument is given, the script validates the WCF core (default).
 * When a package directory is given, it looks for:
 *   - Install files: <dir>/files{_suffix}/acp/database/install_*.php
 *   - Data dirs:     <dir>/files{_suffix}/lib/data
 *
 * Exit codes:
 *   0 = all valid
 *   1 = issues found
 *   2 = configuration error
 */

$baseDir = __DIR__;

if (isset($argv[1])) {
    $packageDir = realpath($argv[1]);
    if ($packageDir === false || !is_dir($packageDir)) {
        fwrite(STDERR, "Error: Directory not found: {$argv[1]}\n");
        exit(2);
    }

    // Find install file(s) in files*/acp/database/.
    $installFiles = glob($packageDir . '/files*/acp/database/install_*.php');
    if (empty($installFiles)) {
        fwrite(STDERR, "Error: No files*/acp/database/install_*.php found in {$packageDir}\n");
        exit(2);
    }

    $installFile = $installFiles[0];

    // Find data directories in files*/lib/data/.
    $dataDirs = glob($packageDir . '/files*/lib/data', \GLOB_ONLYDIR);
    if (empty($dataDirs)) {
        fwrite(STDERR, "Error: No files*/lib/data/ directory found in {$packageDir}\n");
        exit(2);
    }
} else {
    $installFile = $baseDir . '/../wcfsetup/setup/db/install_com.woltlab.wcf.php';
    $dataDirs = [$baseDir . '/../wcfsetup/install/files/lib/data'];
}

// Column types that are NOT NULL by default (convenience/factory classes).
const NOT_NULL_COLUMN_TYPES = [
    'ObjectIdDatabaseTableColumn',
    'NotNullInt10DatabaseTableColumn',
    'NotNullVarchar191DatabaseTableColumn',
    'NotNullVarchar255DatabaseTableColumn',
    'DefaultFalseBooleanDatabaseTableColumn',
    'DefaultTrueBooleanDatabaseTableColumn',
];

// Column types that represent booleans and should be documented as "0|1".
const BOOLEAN_COLUMN_TYPES = [
    'DefaultFalseBooleanDatabaseTableColumn',
    'DefaultTrueBooleanDatabaseTableColumn',
];

// Map column type class prefix to PHP base type.
const COLUMN_PHP_TYPE_MAP = [
    'ObjectId' => 'int',
    'NotNullInt10' => 'int',
    'Int' => 'int',
    'Bigint' => 'int',
    'Mediumint' => 'int',
    'Smallint' => 'int',
    'Tinyint' => 'int',
    'DefaultFalseBoolean' => 'int',
    'DefaultTrueBoolean' => 'int',
    'NotNullVarchar191' => 'string',
    'NotNullVarchar255' => 'string',
    'Varchar' => 'string',
    'Char' => 'string',
    'Text' => 'string',
    'Mediumtext' => 'string',
    'Mediumblob' => 'string',
    'Binary' => 'string',
    'Varbinary' => 'string',
    'Date' => 'string',
    'Datetime' => 'string',
    'Decimal' => 'string',
    'Enum' => 'string',
    'Float' => 'float',
];

// ─── Install File Parsing ────────────────────────────────────────────

/**
 * Parses the install file and returns a map of table name → column definitions.
 *
 * @return array<string, array<string, array{columnType: string, notNull: bool, basePhpType: string}>>
 */
function parseInstallFile(string $filePath): array
{
    $content = file_get_contents($filePath);
    if ($content === false) {
        fwrite(STDERR, "Error: Cannot read install file: {$filePath}\n");
        exit(2);
    }

    $tables = [];
    $offset = 0;

    while (($pos = strpos($content, "DatabaseTable::create('", $offset)) !== false) {
        $nameStart = $pos + \strlen("DatabaseTable::create('");
        $nameEnd = strpos($content, "'", $nameStart);
        if ($nameEnd === false) {
            break;
        }
        $tableName = substr($content, $nameStart, $nameEnd - $nameStart);

        // Extract text until the next DatabaseTable::create or end of file.
        $nextTablePos = strpos($content, "DatabaseTable::create('", $nameEnd);
        $tableBlock = $nextTablePos !== false
            ? substr($content, $nameEnd, $nextTablePos - $nameEnd)
            : substr($content, $nameEnd);

        // Find the ->columns([...]) section.
        $columnsMarker = '->columns([';
        $columnsStart = strpos($tableBlock, $columnsMarker);
        if ($columnsStart !== false) {
            $columnsStart += \strlen($columnsMarker);

            // Find matching ]).
            $depth = 1;
            $p = $columnsStart;
            $len = \strlen($tableBlock);
            while ($depth > 0 && $p < $len) {
                if ($tableBlock[$p] === '[') {
                    $depth++;
                } elseif ($tableBlock[$p] === ']') {
                    $depth--;
                }
                $p++;
            }
            $columnsContent = substr($tableBlock, $columnsStart, $p - $columnsStart - 1);
            $newColumns = parseColumns($columnsContent);
            $tables[$tableName] = isset($tables[$tableName])
                ? array_merge($tables[$tableName], $newColumns)
                : $newColumns;
        }

        $offset = $nameEnd + 1;
    }

    return $tables;
}

/**
 * Parses individual column definitions from the content of a ->columns([...]) block.
 *
 * @return array<string, array{columnType: string, notNull: bool, basePhpType: string}>
 */
function parseColumns(string $content): array
{
    $columns = [];

    \preg_match_all(
        '/(\w+DatabaseTableColumn)::create\(\'(\w+)\'\)/',
        $content,
        $matches,
        \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE,
    );

    foreach ($matches as $i => $match) {
        $columnType = $match[1][0];
        $columnName = $match[2][0];

        // Extract the method chain following this column definition.
        $chainStart = $match[0][1] + \strlen($match[0][0]);
        $chainEnd = isset($matches[$i + 1])
            ? $matches[$i + 1][0][1]
            : \strlen($content);
        $chain = substr($content, $chainStart, $chainEnd - $chainStart);

        // Determine NOT NULL status.
        $isNotNull = \in_array($columnType, NOT_NULL_COLUMN_TYPES, true)
            || (bool)\preg_match('/->notNull\(\s*\)/', $chain)
            || (bool)\preg_match('/->notNull\(\s*true\s*\)/', $chain);

        if (\preg_match('/->notNull\(\s*false\s*\)/', $chain)) {
            $isNotNull = false;
        }

        $shortType = str_replace('DatabaseTableColumn', '', $columnType);
        $basePhpType = COLUMN_PHP_TYPE_MAP[$shortType] ?? 'mixed';

        $columns[$columnName] = [
            'columnType' => $columnType,
            'notNull' => $isNotNull,
            'isBoolean' => \in_array($columnType, BOOLEAN_COLUMN_TYPES, true),
            'basePhpType' => $basePhpType,
        ];
    }

    return $columns;
}

// ─── Class Discovery ─────────────────────────────────────────────────

/**
 * Scans all PHP class files in the data directory and collects raw class info.
 *
 * @return array<string, array{className: string, namespace: string, file: string, parentRef: ?string, isAbstract: bool, ownProperties: array, useImports: array}>
 *         Keyed by FQCN.
 */
function scanAllClassFiles(string $dataDir): array
{
    $allClasses = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dataDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        /** @var SplFileInfo $file */
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }

        // Extract namespace.
        if (!\preg_match('/namespace\s+([\w\\\\]+)\s*;/', $content, $nsMatch)) {
            continue;
        }
        $namespace = $nsMatch[1];

        // Extract class declaration (anchored to line start to avoid matching comments).
        $isAbstract = false;
        if (\preg_match('/^\s*((?:(?:abstract|final|readonly)\s+)*)class\s+(\w+)(?:\s+extends\s+([\w\\\\]+))?/m', $content, $classMatch)) {
            $modifiers = $classMatch[1];
            $className = $classMatch[2];
            $parentRef = $classMatch[3] ?? null;
            $isAbstract = str_contains($modifiers, 'abstract');
        } else {
            continue;
        }

        // Collect use imports for FQCN resolution.
        $useImports = [];
        \preg_match_all('/^\s*use\s+([\w\\\\]+)(?:\s+as\s+(\w+))?\s*;/m', $content, $useMatches, \PREG_SET_ORDER);
        foreach ($useMatches as $useMatch) {
            $importFqcn = $useMatch[1];
            $alias = $useMatch[2] ?? null;
            $shortName = $alias ?? substr($importFqcn, strrpos($importFqcn, '\\') + 1);
            $useImports[$shortName] = $importFqcn;
        }

        // Extract @property-read annotations.
        $ownProperties = parsePropertyReadAnnotations($content);

        // Check for custom $databaseTableName.
        $customTableName = null;
        if (\preg_match('/protected\s+static\s+\$databaseTableName\s*=\s*\'([^\']+)\'/', $content, $tblMatch)) {
            $customTableName = $tblMatch[1];
        }

        $fqcn = $namespace . '\\' . $className;
        $allClasses[$fqcn] = [
            'className' => $className,
            'namespace' => $namespace,
            'file' => $file->getPathname(),
            'parentRef' => $parentRef,
            'isAbstract' => $isAbstract,
            'ownProperties' => $ownProperties,
            'useImports' => $useImports,
            'customTableName' => $customTableName,
        ];
    }

    return $allClasses;
}

/**
 * Resolves a parent class reference to a FQCN.
 */
function resolveParentFqcn(string $parentRef, string $currentNamespace, array $useImports): string
{
    // Fully qualified.
    if (str_starts_with($parentRef, '\\')) {
        return ltrim($parentRef, '\\');
    }

    // Check use imports (match the first part before any backslash).
    $firstPart = str_contains($parentRef, '\\')
        ? substr($parentRef, 0, strpos($parentRef, '\\'))
        : $parentRef;

    if (isset($useImports[$firstPart])) {
        if ($firstPart === $parentRef) {
            return $useImports[$firstPart];
        }
        // Aliased namespace prefix.
        return $useImports[$firstPart] . substr($parentRef, \strlen($firstPart));
    }

    // Same namespace.
    return $currentNamespace . '\\' . $parentRef;
}

/**
 * Recursively collects all @property-read annotations including inherited ones.
 *
 * @param array<string, array> $allClasses
 * @param array<string, bool>  $visited     Cycle guard
 * @return array<string, array{type: string, nullable: bool}>
 */
function collectInheritedProperties(string $fqcn, array $allClasses, array &$visited = []): array
{
    if (isset($visited[$fqcn]) || !isset($allClasses[$fqcn])) {
        return [];
    }
    $visited[$fqcn] = true;

    $classInfo = $allClasses[$fqcn];
    $parentProperties = [];

    if ($classInfo['parentRef'] !== null) {
        $parentFqcn = resolveParentFqcn(
            $classInfo['parentRef'],
            $classInfo['namespace'],
            $classInfo['useImports'],
        );
        $parentProperties = collectInheritedProperties($parentFqcn, $allClasses, $visited);
    }

    // Child properties override parent.
    return array_merge($parentProperties, $classInfo['ownProperties']);
}

/**
 * Builds the table name → class info map with merged (inherited) properties.
 *
 * @return array<string, array{className: string, namespace: string, file: string, properties: array}>
 */
function buildClassMap(array $allClasses, string $tablePrefix): array
{
    $classes = [];

    foreach ($allClasses as $fqcn => $info) {
        // Skip abstract classes.
        if ($info['isAbstract']) {
            continue;
        }

        $tableName = computeTableName($info['className'], $info['customTableName'], $tablePrefix);

        // Collect own + inherited properties.
        $visited = [];
        $mergedProperties = collectInheritedProperties($fqcn, $allClasses, $visited);

        if (empty($mergedProperties)) {
            continue;
        }

        $classes[$tableName] = [
            'className' => $info['className'],
            'namespace' => $info['namespace'],
            'file' => $info['file'],
            'properties' => $mergedProperties,
        ];
    }

    return $classes;
}

/**
 * Computes the database table name from a class name, mirroring
 * DatabaseObject::getDatabaseTableName().
 */
function computeTableName(string $className, ?string $customTableName, string $tablePrefix): string
{
    if ($customTableName !== null && $customTableName !== '') {
        return $tablePrefix . $customTableName;
    }

    $parts = \preg_split(
        '~(?=[A-Z](?=[a-z]))~',
        $className,
        -1,
        \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY,
    );

    return $tablePrefix . strtolower(implode('_', $parts));
}

/**
 * Detects the most common table prefix from the parsed install file tables.
 *
 * Falls back to 'wcf1_' if no clear prefix is found.
 */
function detectTablePrefix(array $tables): string
{
    $prefixCounts = [];
    foreach (array_keys($tables) as $tableName) {
        if (\preg_match('/^(\w+1_)/', $tableName, $m)) {
            $prefix = $m[1];
            $prefixCounts[$prefix] = ($prefixCounts[$prefix] ?? 0) + 1;
        }
    }

    if (empty($prefixCounts)) {
        return 'wcf1_';
    }

    arsort($prefixCounts);

    return array_key_first($prefixCounts);
}

/**
 * Extracts @property-read annotations from a class file's PHPDoc block.
 *
 * @return array<string, array{type: string, nullable: bool}>
 */
function parsePropertyReadAnnotations(string $content): array
{
    $properties = [];

    if (\preg_match_all('/@property-read\s+(\S+)\s+\$(\w+)/', $content, $matches, \PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $type = $match[1];
            $properties[$match[2]] = [
                'type' => $type,
                'nullable' => isNullableType($type),
            ];
        }
    }

    return $properties;
}

// ─── Type Helpers ────────────────────────────────────────────────────

function isNullableType(string $type): bool
{
    return str_starts_with($type, '?')
        || str_contains($type, '|null')
        || str_starts_with($type, 'null|');
}

/**
 * Extracts the base type from a PHPDoc type, stripping nullable markers.
 *
 * Examples: "?int" → "int", "int|null" → "int", "1|0" → "1|0"
 */
function extractBaseType(string $type): string
{
    $type = ltrim($type, '?');
    $parts = explode('|', $type);
    $parts = array_values(array_filter($parts, static fn(string $p): bool => $p !== 'null'));

    return implode('|', $parts);
}

/**
 * Checks whether a documented type is compatible with the expected base PHP type.
 */
function isBaseTypeCompatible(string $docType, string $expectedBaseType): bool
{
    $base = extractBaseType($docType);

    if ($base === $expectedBaseType) {
        return true;
    }

    // Accept "1|0" and "0|1" as compatible with int (boolean-style columns).
    if ($expectedBaseType === 'int' && \in_array($base, ['1|0', '0|1'], true)) {
        return true;
    }

    return false;
}

// ─── Validation ──────────────────────────────────────────────────────

/**
 * @return list<array{class: string, file: string, type: string, column: string, detail: string}>
 */
function validate(array $tables, array $classes): array
{
    $issues = [];

    foreach ($classes as $tableName => $classInfo) {
        if (!isset($tables[$tableName])) {
            // Table not defined in install file — may be from another package.
            continue;
        }

        $dbColumns = $tables[$tableName];
        $docProperties = $classInfo['properties'];
        $label = $classInfo['namespace'] . '\\' . $classInfo['className'];
        $file = $classInfo['file'];

        // 1. Columns missing from @property-read.
        foreach ($dbColumns as $colName => $colDef) {
            if (!isset($docProperties[$colName])) {
                $expectedType = $colDef['notNull']
                    ? $colDef['basePhpType']
                    : '?' . $colDef['basePhpType'];
                $issues[] = [
                    'class' => $label,
                    'file' => $file,
                    'type' => 'MISSING',
                    'column' => $colName,
                    'detail' => "Column \${$colName} has no @property-read (expected type: {$expectedType})",
                ];
            }
        }

        // 2. @property-read entries that have no matching column.
        foreach ($docProperties as $propName => $propDef) {
            if (!isset($dbColumns[$propName])) {
                $issues[] = [
                    'class' => $label,
                    'file' => $file,
                    'type' => 'EXTRA',
                    'column' => $propName,
                    'detail' => "@property-read \${$propName} has no matching column in the schema",
                ];
            }
        }

        // 3. Type/nullability mismatches.
        foreach ($dbColumns as $colName => $colDef) {
            if (!isset($docProperties[$colName])) {
                continue;
            }

            $docType = $docProperties[$colName]['type'];
            $docNullable = $docProperties[$colName]['nullable'];
            $dbNullable = !$colDef['notNull'];

            if ($docNullable !== $dbNullable) {
                $expected = $dbNullable
                    ? '?' . $colDef['basePhpType']
                    : $colDef['basePhpType'];
                $issues[] = [
                    'class' => $label,
                    'file' => $file,
                    'type' => 'NULLABILITY',
                    'column' => $colName,
                    'detail' => "\${$colName} is documented as {$docType} but column is "
                        . ($dbNullable ? 'nullable' : 'NOT NULL')
                        . " (expected: {$expected})",
                ];
            }

            if (!isBaseTypeCompatible($docType, $colDef['basePhpType'])) {
                $issues[] = [
                    'class' => $label,
                    'file' => $file,
                    'type' => 'TYPE',
                    'column' => $colName,
                    'detail' => "\${$colName} is documented as {$docType} but column type {$colDef['columnType']} maps to {$colDef['basePhpType']}",
                ];
            }

            // 4. Boolean columns must be documented as "0|1".
            if ($colDef['isBoolean'] && extractBaseType($docType) !== '0|1') {
                $issues[] = [
                    'class' => $label,
                    'file' => $file,
                    'type' => 'BOOLEAN',
                    'column' => $colName,
                    'detail' => "\${$colName} is documented as {$docType} but boolean columns should use 0|1",
                ];
            }
        }
    }

    // Sort by class, then type, then column.
    usort($issues, static function (array $a, array $b): int {
        return $a['class'] <=> $b['class']
            ?: $a['type'] <=> $b['type']
            ?: $a['column'] <=> $b['column'];
    });

    return $issues;
}

// ─── Reporting ───────────────────────────────────────────────────────

function report(array $issues, array $tables, array $classes, string $baseDir): void
{
    $matchedTables = 0;
    $unmatchedClasses = [];
    foreach ($classes as $tableName => $classInfo) {
        if (isset($tables[$tableName])) {
            $matchedTables++;
        } else {
            $unmatchedClasses[] = $classInfo['namespace'] . '\\' . $classInfo['className']
                . " (computed table: {$tableName})";
        }
    }

    echo "Database @property-read Validation\n";
    echo str_repeat('=', 60) . "\n\n";
    echo "Tables in install file:        " . \count($tables) . "\n";
    echo "Classes with @property-read:   " . \count($classes) . "\n";
    echo "Matched (table ↔ class):       {$matchedTables}\n";
    echo "\n";

    if (empty($issues)) {
        echo "\033[32m✓ All @property-read annotations are up to date.\033[0m\n";
        return;
    }

    // Group issues by class.
    $grouped = [];
    foreach ($issues as $issue) {
        $grouped[$issue['class']][] = $issue;
    }

    $counts = ['MISSING' => 0, 'EXTRA' => 0, 'NULLABILITY' => 0, 'TYPE' => 0, 'BOOLEAN' => 0];

    foreach ($grouped as $class => $classIssues) {
        $file = str_replace($baseDir . '/', '', $classIssues[0]['file']);
        echo "\033[1m{$class}\033[0m\n";
        echo "  {$file}\n\n";

        foreach ($classIssues as $issue) {
            $counts[$issue['type']]++;
            $color = match ($issue['type']) {
                'MISSING' => "\033[31m",     // red
                'EXTRA' => "\033[33m",       // yellow
                'NULLABILITY' => "\033[35m", // magenta
                'TYPE' => "\033[36m",        // cyan
                'BOOLEAN' => "\033[34m",     // blue
            };
            echo "  {$color}[{$issue['type']}]\033[0m {$issue['detail']}\n";
        }
        echo "\n";
    }

    echo str_repeat('─', 60) . "\n";
    echo "Summary:\n";
    echo "  Missing @property-read:    {$counts['MISSING']}\n";
    echo "  Extra @property-read:      {$counts['EXTRA']}\n";
    echo "  Nullability mismatches:    {$counts['NULLABILITY']}\n";
    echo "  Base type mismatches:      {$counts['TYPE']}\n";
    echo "  Boolean type mismatches:   {$counts['BOOLEAN']}\n";
    echo "  Total issues:              " . \count($issues) . "\n";

    if (!empty($unmatchedClasses)) {
        echo "\nClasses with @property-read but no matching table in install file:\n";
        foreach ($unmatchedClasses as $c) {
            echo "  - {$c}\n";
        }
    }
}

// ─── Main ────────────────────────────────────────────────────────────

$tables = parseInstallFile($installFile);
$tablePrefix = detectTablePrefix($tables);
$allClasses = [];
foreach ($dataDirs as $dataDir) {
    $allClasses = array_merge($allClasses, scanAllClassFiles($dataDir));
}
$classes = buildClassMap($allClasses, $tablePrefix);
$issues = validate($tables, $classes);
report($issues, $tables, $classes, $baseDir);

exit(empty($issues) ? 0 : 1);

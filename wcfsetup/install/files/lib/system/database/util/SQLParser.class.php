<?php

namespace wcf\system\database\util;

use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * SQLParser takes many sql queries in a simple mysql syntax.
 * Given queries will be parsed, converted and executed in the active database.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SQLParser
{
    /**
     * list of sql queries
     * @var string[]
     */
    protected $queryArray = [];

    /**
     * Creates a new SQLParser object.
     *
     * @param string $queries
     */
    public function __construct($queries)
    {
        // delete comments
        $queries = \preg_replace("~('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(?:(?:--|#)[^\n]*|/\\*.*?\\*/)~s", '$1', $queries);

        // split queries by semicolon
        if (\preg_match_all("~(?:[^;']+(?:'[^'\\\\]*(?:\\\\.[^'\\\\]*)*')*)*(?=;|\$)~s", $queries, $matches)) {
            $this->queryArray = ArrayUtil::trim($matches[0]);
        }
    }

    /**
     * Executes the sql queries.
     */
    public function execute()
    {
        foreach ($this->queryArray as $query) {
            if (
                \preg_match(
                    '~^(ALTER\s+TABLE|CREATE\s+INDEX|CREATE\s+TABLE|DROP\s+INDEX|DROP\s+TABLE|INSERT|UPDATE|DELETE)~i',
                    $query,
                    $match
                )
            ) {
                $statement = \strtoupper(\preg_replace('~\s+~', ' ', $match[0]));

                $this->executeStatement($statement, $query);
            }
        }
    }

    /**
     * Executes a sql statement.
     *
     * @param string $statement
     * @param string $query
     * @throws  SystemException
     */
    protected function executeStatement($statement, $query)
    {
        switch ($statement) {
            case 'CREATE TABLE':
                // get table name
                if (\preg_match('~^CREATE\s+TABLE\s+(\w+)\s*\(~i', $query, $match)) {
                    $tableName = $match[1];
                    $columns = $indices = [];

                    // find columns
                    if (
                        \preg_match_all(
                            "~(?:\\(|,)\\s*(\\w+)\\s+(\\w+)(?:\\s*\\((\\s*(?:\\d+(?:\\s*,\\s*\\d+)?|'[^']*'(?:\\s*,\\s*'[^']*')*))\\s*\\))?(?:\\s+UNSIGNED)?(?:\\s+(NOT NULL|NULL))?(?:\\s+DEFAULT\\s+(\\d+.\\d+|\\d+|NULL|'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'))?(?:\\s+(AUTO_INCREMENT))?(?:\\s+(UNIQUE|PRIMARY)(?: KEY)?)?~i",
                            $query,
                            $matches
                        )
                    ) {
                        for ($i = 0, $j = \count($matches[0]); $i < $j; $i++) {
                            $columName = \strtoupper($matches[1][$i]);
                            if (
                                $columName == 'UNIQUE'
                                || $columName == 'KEY'
                                || $columName == 'PRIMARY'
                                || $columName == 'FULLTEXT'
                            ) {
                                break;
                            }

                            $column = ['name' => $matches[1][$i]];
                            $columnType = \strtolower($matches[2][$i]);
                            $column['data'] = [
                                'type' => $columnType,
                                'notNull' => (!empty($matches[4][$i]) && \strtoupper($matches[4][$i]) == 'NOT NULL') ? true : false,
                                'default' => $matches[5][$i],
                                'autoIncrement' => !empty($matches[6][$i]) ? true : false,
                                'key' => \strtoupper($matches[7][$i]),
                            ];
                            if (!empty($matches[3][$i])) {
                                if ($columnType == 'enum') {
                                    $column['data']['values'] = $matches[3][$i];
                                } else {
                                    if (
                                        \preg_match(
                                            '~^(\d+)(?:\s*,\s*(\d+))?$~',
                                            StringUtil::trim($matches[3][$i]),
                                            $match2
                                        )
                                    ) {
                                        $column['data']['length'] = $match2[1];
                                        if (!empty($match2[2])) {
                                            $column['data']['decimals'] = $match2[2];
                                        }
                                    } else {
                                        throw new SystemException("Unsupported SQL statement '" . $query . "'");
                                    }
                                }
                            }

                            $columns[] = $column;
                        }
                    } else {
                        throw new SystemException("Unsupported SQL statement '" . $query . "'");
                    }

                    // find indices
                    if (
                        \preg_match_all(
                            '~(?:\(|,)\s*(?:(?:(?:(UNIQUE|FULLTEXT)(?:\s+(?:INDEX|KEY))?|(?:INDEX|KEY))(?:\s+(\w+))?)|(PRIMARY) KEY)\s+\((\s*\w+\s*(?:,\s*\w+\s*)*)\)~is',
                            $query,
                            $matches
                        )
                    ) {
                        for ($i = 0, $j = \count($matches[0]); $i < $j; $i++) {
                            $index = ['name' => $matches[2][$i], 'data' => []];
                            $index['data']['type'] = \strtoupper((!empty($matches[1][$i]) ? $matches[1][$i] : $matches[3][$i]));
                            $index['data']['columns'] = $matches[4][$i];
                            $indices[] = $index;
                        }
                    }

                    $this->executeCreateTableStatement($tableName, $columns, $indices);
                }
                break;

            case 'ALTER TABLE':
                // add index
                if (
                    \preg_match(
                        '~^ALTER\s+TABLE\s+(\w+)\s+ADD\s+(?:(UNIQUE|FULLTEXT)\s+)?(?:INDEX|KEY)\s+(?:(\w+)\s*)?\((\s*\w+\s*(?:,\s*\w+\s*)*)\)~is',
                        $query,
                        $match
                    )
                ) {
                    $this->executeAddIndexStatement(
                        $match[1],
                        ($match[3] ?: self::getGenericIndexName($match[1], $match[4])),
                        ['type' => \strtoupper($match[2]), 'columns' => $match[4]]
                    );
                } // add foreign key
                elseif (
                    \preg_match(
                        '~^ALTER\s+TABLE\s+(\w+)\s+ADD\s+FOREIGN KEY\s+(?:(\w+)\s*)?\((\s*\w+\s*(?:,\s*\w+\s*)*)\)\s+REFERENCES\s+(\w+)\s+\((\s*\w+\s*(?:,\s*\w+\s*)*)\)(?:\s+ON\s+DELETE\s+(CASCADE|SET NULL|NO ACTION))?(?:\s+ON\s+UPDATE\s+(CASCADE|SET NULL|NO ACTION))?~is',
                        $query,
                        $match
                    )
                ) {
                    $this->executeAddForeignKeyStatement(
                        $match[1],
                        ($match[2] ?: self::getGenericIndexName($match[1], $match[3], 'fk')),
                        [
                            'columns' => $match[3],
                            'referencedTable' => $match[4],
                            'referencedColumns' => $match[5],
                            'ON DELETE' => $match[6] ?? '',
                            'ON UPDATE' => $match[7] ?? '',
                        ]
                    );
                } // add/change column
                elseif (
                    \preg_match(
                        "~^ALTER\\s+TABLE\\s+(\\w+)\\s+(?:(ADD)\\s+(?:COLUMN\\s+)?|(CHANGE)\\s+(?:COLUMN\\s+)?(\\w+)\\s+)(\\w+)\\s+(\\w+)(?:\\s*\\((\\s*(?:\\d+(?:\\s*,\\s*\\d+)?|'[^']*'(?:\\s*,\\s*'[^']*')*))\\s*\\))?(?:\\s+UNSIGNED)?(?:\\s+(NOT NULL|NULL))?(?:\\s+DEFAULT\\s+(-?\\d+.\\d+|-?\\d+|NULL|'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'))?(?:\\s+(AUTO_INCREMENT))?(?:\\s+(UNIQUE|PRIMARY)(?: KEY)?)?~is",
                        $query,
                        $match
                    )
                ) {
                    $columnType = \strtolower($match[6]);
                    $columnData = [
                        'type' => $columnType,
                        'notNull' => (!empty($match[8]) && \strtoupper($match[8]) == 'NOT NULL') ? true : false,
                        'default' => $match[9] ?? '',
                        'autoIncrement' => !empty($match[10]) ? true : false,
                        'key' => !empty($match[11]) ? \strtoupper($match[11]) : '',
                    ];
                    if (!empty($match[7])) {
                        if ($columnType == 'enum') {
                            $columnData['values'] = $match[7];
                        } else {
                            if (\preg_match('~^(\d+)(?:\s*,\s*(\d+))?$~', StringUtil::trim($match[7]), $match2)) {
                                $columnData['length'] = $match2[1];
                                if (!empty($match2[2])) {
                                    $columnData['decimals'] = $match2[2];
                                }
                            } else {
                                throw new SystemException("Unsupported SQL statement '" . $query . "'");
                            }
                        }
                    }

                    if (\strtoupper($match[2]) == 'ADD') {
                        $this->executeAddColumnStatement($match[1], $match[5], $columnData);
                    } else {
                        $this->executeAlterColumnStatement($match[1], $match[4], $match[5], $columnData);
                    }
                } // drop index
                elseif (\preg_match('~^ALTER\s+TABLE\s+(\w+)\s+DROP\s+(?:INDEX|KEY)\s+(\w+)~is', $query, $match)) {
                    $this->executeDropIndexStatement($match[1], $match[2]);
                } // drop primary key
                elseif (\preg_match('~^ALTER\s+TABLE\s+(\w+)\s+DROP\s+PRIMARY\s+KEY~is', $query, $match)) {
                    $this->executeDropPrimaryKeyStatement($match[1]);
                } // drop foreign key
                elseif (\preg_match('~^ALTER\s+TABLE\s+(\w+)\s+DROP\s+FOREIGN KEY\s+(\w+)~is', $query, $match)) {
                    $this->executeDropForeignKeyStatement(
                        $match[1],
                        self::getGenericIndexName($match[1], $match[2], 'fk')
                    );
                } // drop column
                elseif (\preg_match('~^ALTER\s+TABLE\s+(\w+)\s+DROP\s+(?:COLUMN\s+)?(\w+)~is', $query, $match)) {
                    $this->executeDropColumnStatement($match[1], $match[2]);
                } else {
                    throw new SystemException("Unsupported SQL statement '" . $query . "'");
                }
                break;

            case 'CREATE INDEX':
                if (
                    \preg_match(
                        '~^CREATE\s+(?:(UNIQUE|FULLTEXT)\s+)?INDEX\s+(\w+)\s+ON\s+(\w+)\s+\((\s*\w+\s*(?:,\s*\w+\s*)*)\)~is',
                        $query,
                        $match
                    )
                ) {
                    $this->executeAddIndexStatement(
                        $match[3],
                        ($match[2] ?: self::getGenericIndexName($match[3], $match[4])),
                        ['type' => \strtoupper($match[1]), 'columns' => $match[4]]
                    );
                } else {
                    throw new SystemException("Unsupported SQL statement '" . $query . "'");
                }
                break;

            case 'DROP INDEX':
                if (\preg_match('~^DROP\s+INDEX\s+(\w+)\s+ON\s+(\w+)~i', $query, $match)) {
                    $this->executeDropIndexStatement($match[2], $match[1]);
                } else {
                    throw new SystemException("Unsupported SQL statement '" . $query . "'");
                }
                break;

            case 'DROP TABLE':
                if (\preg_match('~^DROP\s+TABLE\s+(?:IF\s+EXISTS\s+)?(\w+)~i', $query, $match)) {
                    $this->executeDropTableStatement($match[1]);
                } else {
                    throw new SystemException("Unsupported SQL statement '" . $query . "'");
                }
                break;

            case 'INSERT': // standard sql; execute directly
            case 'UPDATE':
            case 'DELETE':
                $this->executeStandardStatement($query);
                break;
        }
    }

    /**
     * Executes a 'CREATE TABLE' statement.
     *
     * @param string $tableName
     * @param array $columns
     * @param array $indices
     */
    protected function executeCreateTableStatement($tableName, $columns, $indices = [])
    {
        WCF::getDB()->getEditor()->createTable($tableName, $columns, $indices);
    }

    /**
     * Executes an 'ALTER TABLE ... ADD COLUMN' statement.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $columnData
     */
    protected function executeAddColumnStatement($tableName, $columnName, $columnData)
    {
        WCF::getDB()->getEditor()->addColumn($tableName, $columnName, $columnData);
    }

    /**
     * Executes an 'ALTER TABLE ... CHANGE COLUMN' statement.
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $newColumnData
     */
    protected function executeAlterColumnStatement($tableName, $oldColumnName, $newColumnName, $newColumnData)
    {
        WCF::getDB()->getEditor()->alterColumn($tableName, $oldColumnName, $newColumnName, $newColumnData);
    }

    /**
     * Executes a 'CREATE INDEX' statement.
     *
     * @param string $tableName
     * @param string $indexName
     * @param array $indexData
     */
    protected function executeAddIndexStatement($tableName, $indexName, $indexData)
    {
        WCF::getDB()->getEditor()->addIndex($tableName, $indexName, $indexData);
    }

    /**
     * Executes a 'ALTER TABLE ... ADD FOREIGN KEY' statement.
     *
     * @param string $tableName
     * @param string $indexName
     * @param array $indexData
     */
    protected function executeAddForeignKeyStatement($tableName, $indexName, $indexData)
    {
        WCF::getDB()->getEditor()->addForeignKey($tableName, $indexName, $indexData);
    }

    /**
     * Executes an 'ALTER TABLE ... DROP COLUMN' statement.
     *
     * @param string $tableName
     * @param string $columnName
     */
    protected function executeDropColumnStatement($tableName, $columnName)
    {
        WCF::getDB()->getEditor()->dropColumn($tableName, $columnName);
    }

    /**
     * Executes a 'DROP INDEX' statement.
     *
     * @param string $tableName
     * @param string $indexName
     */
    protected function executeDropIndexStatement($tableName, $indexName)
    {
        WCF::getDB()->getEditor()->dropIndex($tableName, $indexName);
    }

    /**
     * Executes a 'DROP PRIMARY KEY' statement.
     *
     * @param string $tableName
     */
    protected function executeDropPrimaryKeyStatement($tableName)
    {
        WCF::getDB()->getEditor()->dropPrimaryKey($tableName);
    }

    /**
     * Executes a 'DROP FOREIGN KEY' statement.
     *
     * @param string $tableName
     * @param string $indexName
     */
    protected function executeDropForeignKeyStatement($tableName, $indexName)
    {
        WCF::getDB()->getEditor()->dropForeignKey($tableName, $indexName);
    }

    /**
     * Executes a 'DROP TABLE' statement.
     *
     * @param string $tableName
     */
    protected function executeDropTableStatement($tableName)
    {
        WCF::getDB()->getEditor()->dropTable($tableName);
    }

    /**
     * Executes a standard ansi sql statement.
     *
     * @param string $query
     */
    protected function executeStandardStatement($query)
    {
        $statement = WCF::getDB()->prepare($query);
        $statement->execute();
    }

    /**
     * Creates a generic index name.
     *
     * @param string $tableName
     * @param string $columns
     * @param string $suffix
     * @return  string      index name
     */
    protected static function getGenericIndexName($tableName, $columns, $suffix = '')
    {
        // get first column
        $columns = ArrayUtil::trim(\explode(',', $columns));

        return \md5($tableName . '_' . \reset($columns)) . ($suffix ? '_' . $suffix : '');
    }
}

<?php

namespace wcf\system\search\mysql;

use wcf\system\database\DatabaseException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\search\AbstractSearchEngine;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Search engine using MySQL's FULLTEXT index.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search
 */
class MysqlSearchEngine extends AbstractSearchEngine
{
    /**
     * MySQL's minimum word length for fulltext indices
     * @var int
     */
    protected $ftMinWordLen;

    /**
     * @inheritDoc
     */
    protected $specialCharacters = ['(', ')', '@', '+', '-', '"', '<', '>', '~', '*'];

    /**
     * @inheritDoc
     */
    public function search(
        $q,
        array $objectTypes,
        $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $additionalConditions = [],
        $orderBy = 'time DESC',
        $limit = 1000
    ) {
        // build search query
        $sql = '';
        $parameters = [];
        foreach ($objectTypes as $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);

            if (!empty($sql)) {
                $sql .= "\nUNION ALL\n";
            }
            $additionalConditionsConditionBuilder = ($additionalConditions[$objectTypeName] ?? null);

            $query = $objectType->getOuterSQLQuery($q, $searchIndexCondition, $additionalConditionsConditionBuilder);
            if (empty($query)) {
                $query = "
                    SELECT      " . $objectType->getIDFieldName() . " AS objectID,
                                " . $objectType->getSubjectFieldName() . " AS subject,
                                " . $objectType->getTimeFieldName() . " AS time,
                                " . $objectType->getUsernameFieldName() . " AS username,
                                '" . $objectTypeName . "' AS objectType
                                " . ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC' ? ',search_index.relevance' : '') . "
                    FROM        " . $objectType->getTableName() . "
                    INNER JOIN  ({WCF_SEARCH_INNER_JOIN}) search_index
                    ON          (" . $objectType->getIDFieldName() . " = search_index.objectID)
                    " . $objectType->getJoins() . "
                    " . ($additionalConditions[$objectTypeName] ?? '');
            }

            if (\mb_strpos($query, '{WCF_SEARCH_INNER_JOIN}')) {
                $innerJoin = $this->getInnerJoin(
                    $objectTypeName,
                    $q,
                    $subjectOnly,
                    $searchIndexCondition,
                    $orderBy,
                    $limit
                );

                $query = \str_replace('{WCF_SEARCH_INNER_JOIN}', $innerJoin['sql'], $query);
                if ($innerJoin['fulltextCondition'] !== null) {
                    $parameters = \array_merge($parameters, $innerJoin['fulltextCondition']->getParameters());
                }
            }

            if ($searchIndexCondition !== null) {
                $parameters = \array_merge($parameters, $searchIndexCondition->getParameters());
            }
            if (isset($additionalConditions[$objectTypeName])) {
                $parameters = \array_merge($parameters, $additionalConditions[$objectTypeName]->getParameters());
            }

            $sql .= $query;
        }
        if (empty($sql)) {
            throw new SystemException('no object types given');
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }

        // send search query
        $messages = [];
        $statement = WCF::getDB()->prepareStatement($sql, $limit);
        $statement->execute($parameters);
        while ($row = $statement->fetchArray()) {
            $messages[] = [
                'objectID' => $row['objectID'],
                'objectType' => $row['objectType'],
            ];
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function getInnerJoin(
        $objectTypeName,
        $q,
        $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        $orderBy = 'time DESC',
        $limit = 1000
    ) {
        $fulltextCondition = null;
        $relevanceCalc = '';
        if (!empty($q)) {
            $q = $this->parseSearchQuery($q);

            $fulltextCondition = new PreparedStatementConditionBuilder(false);
            $fulltextCondition->add(
                "MATCH (subject" . (!$subjectOnly ? ', message, metaData' : '') . ") AGAINST (? IN BOOLEAN MODE)",
                [$q]
            );

            if ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC') {
                $relevanceCalc = "MATCH (subject" . (!$subjectOnly ? ', message, metaData' : '') . ") AGAINST ('" . escapeString($q) . "') + (5 / (1 + POW(LN(1 + (" . TIME_NOW . " - time) / 2592000), 2))) AS relevance";
            }
        }

        $sql = "SELECT  objectID
                        " . ($relevanceCalc ? ',' . $relevanceCalc : ", '0' AS relevance") . "
                FROM    " . SearchIndexManager::getTableName($objectTypeName) . "
                WHERE   " . ($fulltextCondition !== null ? $fulltextCondition : '') . "
                " . (($searchIndexCondition !== null && $searchIndexCondition->__toString()) ? ($fulltextCondition !== null ? "AND " : '') . $searchIndexCondition : '') . "
                " . (!empty($orderBy) && $fulltextCondition === null ? 'ORDER BY ' . $orderBy : '') . "
                LIMIT   " . ($limit == 1000 ? SearchEngine::INNER_SEARCH_LIMIT : $limit);

        return [
            'fulltextCondition' => $fulltextCondition,
            'searchIndexCondition' => $searchIndexCondition,
            'sql' => $sql,
        ];
    }

    /**
     * Manipulates the search term by adding prefixes and suffixes.
     *
     * - `test foo` becomes `+test* +foo*`
     * - `test -foo bar` becomes `+test* -foo +bar*`
     * - `test <foo bar` becomes `+test* <foo* +bar*`
     * - `test "foo bar"` becomes `+test* +"foo bar"`
     */
    protected function parseSearchQuery($query)
    {
        $query = StringUtil::trim($query);

        $result = [];
        foreach ($this->splitIntoTerms($query) as $term) {
            [$prefix, $word, $suffix] = $term;

            // Ignore parentheses.
            if ($word === '(' || $word === ')') {
                continue;
            }

            // Add a '+' prefix if no prefix is given.
            if (!$prefix) {
                $prefix = '+';
            }
            if (!$suffix) {
                // Add a '*' suffix if no suffix is given,
                // - the word is not quoted, and
                // - the prefix is not '-'.
                if ($word[0] !== '"' && $prefix !== '-') {
                    $suffix = '*';
                }
            }

            $result[] = $prefix . $word . $suffix;
        }

        return \implode(' ', $result);
    }

    /**
     * Parses the query into separate search terms.
     *
     * The parser is based off the original InnoDB search query parser with
     * a small difference: Prefixes are only understood if they stand right
     * beside the search term. InnoDB allows an arbitrary number of whitespace
     * after the prefix, leading to unexpected results if the search query
     * was copied from a sentence that uses the dash as word separator.
     *
     * The resulting terms should not be split by MySQL when concatenated
     * with spaces and neither should they cause syntax errors.
     *
     * Examples:
     *
     * Query: `Apfel - Banane`
     * Word: |Apfel|
     * Word: |Banane|
     *
     * Query: `Apfel -Banane`
     * Word: |Apfel|
     * Word: -|Banane|
     *
     * Query: ` Apfel `
     * Word: |Apfel|
     *
     * Query: ` Apfel Banane `
     * Word: |Apfel|
     * Word: |Banane|
     *
     * Query: `Apfel*`
     * Word: |Apfel|*
     *
     * Query: `Apfel *`
     * Word: |Apfel|
     *
     * Query: `Apfel * Banane`
     * Word: |Apfel|
     * Word: |Banane|
     *
     * Query: `+-"Apfel Banane"*`
     * Word: -|"Apfel Banane"|
     *
     * Query: `Äpfel Bananen`
     * Word: |Äpfel|
     * Word: |Bananen|
     *
     * Query: `+-*`
     *
     * Query: `"Apfel`
     * Word: |"Apfel"|
     *
     * Query: `"Apfel Banane" @8`
     * Word: |"Apfel Banane"|
     *
     * Query: `Apfel Banane @8`
     * Word: |Apfel|
     * Word: |Banane|
     *
     * Query: `+((+Apfel -Banane) (-Apfel +Banane)) >Clementine`
     * Word: +|(|
     * Word: |(|
     * Word: +|Apfel|
     * Word: -|Banane|
     * Word: |)|
     * Word: |(|
     * Word: -|Apfel|
     * Word: +|Banane|
     * Word: |)|
     * Word: |)|
     * Word: >|Clementine|
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html
     * @see https://github.com/mysql/mysql-server/blob/ee4455a33b10f1b1886044322e4893f587b319ed/storage/innobase/fts/fts0pars.y
     * @see https://github.com/mysql/mysql-server/blob/ee4455a33b10f1b1886044322e4893f587b319ed/storage/innobase/fts/fts0blex.l
     */
    protected function splitIntoTerms($query)
    {
        $state = 'beforePrefix';

        $parentheses = 0;
        $word = "";
        $isQuoted = null;
        $prefix = null;
        $suffix = null;

        for ($i = 0, $max = \strlen($query); $i < $max;) {
            $char = $query[$i];

            // Treat ASCII control characters as spaces.
            if (\ord($query[$i]) < 0x20 || \ord($query[$i]) == 0x7f) {
                $char = " ";
            }

            if ($state === 'beforePrefix') {
                // Skip Whitespace.
                if (
                \in_array($char, [
                    ' ',
                    "\t",
                ])
                ) {
                    $i++;
                    continue;
                }

                // After a word is before a word. Handle the closing parenthesis
                // early on to avoid needing through all the states.
                if ($char === ')') {
                    if ($parentheses > 0) {
                        $word = ')';
                    }
                    $parentheses--;
                    $i++;
                    $state = 'finish';
                    continue;
                }

                $state = 'prefix';

                // No increment, we must interpret the current character as a prefix.
                continue;
            } elseif ($state === 'prefix') {
                if (
                \in_array($char, [
                    '-',
                    '+',
                    '~',
                    '<',
                    '>',
                ])
                ) {
                    // The last prefix character wins.
                    $prefix = $char;
                    $i++;
                    continue;
                } else {
                    $state = 'word';
                    // No increment, we must interpret the current character as a word.
                    continue;
                }
            } elseif ($state === 'word') {
                // Parentheses might have a prefix, so we handle them
                // inside of the 'word' state.
                if ($char === '(') {
                    $word = '(';
                    $parentheses++;
                    $i++;

                    // Immediately go to the finish to allow for parsing the prefix
                    // of the first word within the parenthesis.
                    $state = 'finish';
                    continue;
                }

                // Check whether this word is quoted.
                if ($isQuoted === null) {
                    if ($char === '"') {
                        $isQuoted = true;
                        $word .= $char;
                        $i++;
                        continue;
                    } else {
                        $isQuoted = false;
                    }
                }

                if ($isQuoted) {
                    $word .= $char;
                    if ($char === '"') {
                        $state = 'suffix';
                    }
                    $i++;
                    continue;
                } else {
                    if (\preg_match('/[^" \n*()+\-<>~@%]/', $char)) {
                        $word .= $char;
                        $i++;
                        continue;
                    } else {
                        $state = 'suffix';
                        // No increment, we must interpret the current character as a suffix.
                        continue;
                    }
                }
            } elseif ($state === 'suffix') {
                if (
                    !$isQuoted && \in_array($char, [
                        '*',
                    ])
                ) {
                    $suffix = $char;
                    $i++;
                    continue;
                } elseif ($char == '@') {
                    $state = 'atSign';
                    $i++;
                    continue;
                } else {
                    $state = 'finish';
                    // No increment, we must yield the word and then continue parsing at
                    // the current position to prevent skipping characters.
                    continue;
                }
            } elseif ($state === 'atSign') {
                if (\preg_match('/[0-9]/', $char)) {
                    $i++;
                    continue;
                } else {
                    $state = 'finish';
                    // No increment, we must yield the word and then continue parsing at
                    // the current position to prevent skipping characters.
                    continue;
                }
            } elseif ($state === 'finish') {
                // Yield only if the word is non-empty.
                if ($word) {
                    yield [$prefix, $word, $suffix];
                }

                $state = 'beforePrefix';
                $word = "";
                $isQuoted = null;
                $prefix = null;
                $suffix = null;

                // It's a bit unclear what we need to do for the percent sign.
                // It may not appear within a word, but it is no legal operator either.
                // Just skip it here to prevent infinite loops, due to no state making
                // progress at the percent sign.
                if ($char === '%') {
                    $i++;
                }

                // No increment, we must interpret the current character as a prefix.
                continue;
            } else {
                throw new \Exception('Unreachable');
            }
        }

        // Yield only if the word is non-empty.
        if ($word) {
            // Add missing quote.
            if ($isQuoted && \substr($word, -1) !== '"') {
                $word .= '"';
            }

            yield [$prefix, $word, $suffix];
        }

        // Yield the remaining closing parentheses.
        while ($parentheses-- > 0) {
            yield ['', ')', ''];
        }
    }

    /**
     * @inheritDoc
     */
    protected function getFulltextMinimumWordLength()
    {
        if ($this->ftMinWordLen === null) {
            $sql = "SHOW VARIABLES LIKE 'ft_min_word_len'";

            try {
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute();
                $row = $statement->fetchArray();
            } catch (DatabaseException $e) {
                // fallback if user is disallowed to issue 'SHOW VARIABLES'
                $row = ['Value' => 4];
            }

            $this->ftMinWordLen = $row['Value'];
        }

        return $this->ftMinWordLen;
    }
}

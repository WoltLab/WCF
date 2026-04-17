<?php

namespace wcf\system\database\util;

/**
 * Builds a sql query 'where' condition.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ConditionBuilder
{
    /**
     * must be true to add the 'WHERE' keyword automatically
     * @var bool
     */
    protected $addWhereKeyword = true;

    /**
     * string used for concatenating conditions
     * @var string
     */
    protected $concat = '';

    /**
     * conditions string
     * @var string
     */
    protected $conditions = '';

    public function __construct(bool $addWhereKeyword = true, string $concat = 'AND')
    {
        $this->addWhereKeyword = $addWhereKeyword;
        $this->concat = ($concat == 'OR') ? ' OR ' : ' AND ';
    }

    /**
     * Adds a new condition.
     *
     * @return void
     */
    public function add(mixed $condition)
    {
        $conditions = $condition;
        if (!\is_array($conditions)) {
            $conditions = [$condition];
        }

        foreach ($conditions as $condition) {
            if (!empty($this->conditions)) {
                $this->conditions .= $this->concat;
            }
            $this->conditions .= $condition;
        }
    }

    /**
     * Returns the build condition.
     */
    #[\Override]
    public function __toString(): string
    {
        return (($this->addWhereKeyword && $this->conditions) ? 'WHERE ' : '') . $this->conditions;
    }

    /**
     * Enables / disables the where keyword.
     *
     * @return void
     */
    public function enableWhereKeyword(bool $enable = true)
    {
        $this->addWhereKeyword = $enable;
    }
}

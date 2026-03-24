<?php

namespace wcf\system\database\statement;

/**
 * Similar to the regular `PreparedStatement` class, but throws an exception when trying to read data
 * before executing the statement at least once.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DebugPreparedStatement extends PreparedStatement
{
    /**
     * @var bool
     */
    protected $debugDidExecuteOnce = false;

    #[\Override]
    public function __call($name, $arguments)
    {
        if ($name === 'fetchAll' || $name === 'fetchColumn') {
            $this->debugThrowIfNotExecutedBefore();
        }

        return parent::__call($name, $arguments);
    }

    #[\Override]
    public function execute(array $parameters = [])
    {
        $this->debugDidExecuteOnce = true;

        parent::execute($parameters);
    }

    #[\Override]
    public function fetchArray($type = null)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchArray($type);
    }

    #[\Override]
    public function fetchSingleRow($type = null)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchSingleRow($type);
    }

    #[\Override]
    public function fetchSingleColumn($columnNumber = 0)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchSingleColumn($columnNumber);
    }

    #[\Override]
    public function fetchObject($className)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchObject($className);
    }

    #[\Override]
    public function fetchSingleObject($className)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchSingleObject($className);
    }

    #[\Override]
    public function fetchObjects($className, $keyProperty = null)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchObjects($className, $keyProperty);
    }

    #[\Override]
    public function fetchMap($keyColumn, $valueColumn, $uniqueKey = true)
    {
        $this->debugThrowIfNotExecutedBefore();

        return parent::fetchMap($keyColumn, $valueColumn, $uniqueKey);
    }

    protected function debugThrowIfNotExecutedBefore(): void
    {
        if (!$this->debugDidExecuteOnce) {
            throw new \RuntimeException('Attempted to fetch data from a statement without executing it at least once.');
        }
    }
}

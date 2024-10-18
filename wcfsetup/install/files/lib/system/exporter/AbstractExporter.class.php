<?php

namespace wcf\system\exporter;

use wcf\system\database\MySQLDatabase;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Basic implementation of IExporter.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractExporter implements IExporter
{
    /**
     * additional data
     * @var array
     */
    public $additionalData = [];

    /**
     * database host name
     * @var string
     */
    protected $databaseHost = '';

    /**
     * database username
     * @var string
     */
    protected $databaseUser = '';

    /**
     * database password
     * @var string
     */
    protected $databasePassword = '';

    /**
     * database name
     * @var string
     */
    protected $databaseName = '';

    /**
     * table prefix
     * @var string
     */
    protected $databasePrefix = '';

    /**
     * file system path
     * @var string
     */
    protected $fileSystemPath = '';

    /**
     * database connection
     * @var \wcf\system\database\Database
     */
    protected $database;

    /**
     * object type => method names
     * @var array
     */
    protected $methods = [];

    /**
     * limits for items per run
     * @var int[]
     */
    protected $limits = [];

    /**
     * default limit for items per run
     * @var int
     */
    protected $defaultLimit = 1000;

    /**
     * selected import data
     * @var array
     */
    protected $selectedData = [];

    /**
     * @inheritDoc
     */
    public function setData(
        $databaseHost,
        $databaseUser,
        #[\SensitiveParameter]
        $databasePassword,
        $databaseName,
        $databasePrefix,
        $fileSystemPath,
        $additionalData
    ) {
        $this->databaseHost = $databaseHost;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        $this->databaseName = $databaseName;
        $this->databasePrefix = $databasePrefix;
        $this->fileSystemPath = ($fileSystemPath ? FileUtil::addTrailingSlash($fileSystemPath) : '');
        $this->additionalData = $additionalData;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $host = $this->databaseHost;
        $port = 0;
        if (\preg_match('/^(.+?):(\d+)$/', $host, $matches)) {
            // simple check, does not care for valid ip addresses
            $host = $matches[1];
            $port = $matches[2];
        }

        $this->database = new MySQLDatabase(
            $host,
            $this->databaseUser,
            $this->databasePassword,
            $this->databaseName,
            $port
        );
    }

    /**
     * @inheritDoc
     */
    public function validateDatabaseAccess()
    {
        $this->init();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDatabasePrefix()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function countLoops($objectType)
    {
        if (!isset($this->methods[$objectType]) || !\method_exists($this, 'count' . $this->methods[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "' given");
        }

        $count = \call_user_func([$this, 'count' . $this->methods[$objectType]]);
        $limit = ($this->limits[$objectType] ?? $this->defaultLimit);

        return \ceil($count / $limit);
    }

    /**
     * @inheritDoc
     */
    public function exportData($objectType, $loopCount = 0)
    {
        if (!isset($this->methods[$objectType]) || !\method_exists($this, 'export' . $this->methods[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "' given");
        }

        $limit = ($this->limits[$objectType] ?? $this->defaultLimit);
        \call_user_func([$this, 'export' . $this->methods[$objectType]], $loopCount * $limit, $limit);
    }

    /**
     * @inheritDoc
     */
    public function validateSelectedData(array $selectedData)
    {
        $this->selectedData = $selectedData;

        if (!\count($this->selectedData)) {
            return false;
        }

        $supportedData = $this->getSupportedData();
        foreach ($this->selectedData as $name) {
            if (isset($supportedData[$name])) {
                break;
            }

            foreach ($supportedData as $key => $data) {
                if (\in_array($name, $data)) {
                    if (!\in_array($key, $selectedData)) {
                        return false;
                    }

                    break 2;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Returns the maximum value of a specific column.
     *
     * @param string $tableName
     * @param string $columnName
     * @return  int
     */
    protected function __getMaxID($tableName, $columnName)
    {
        $sql = "SELECT  MAX(" . $columnName . ") AS maxID
                FROM    " . $tableName;
        $statement = $this->database->prepare($sql);
        $statement->execute();
        $row = $statement->fetchArray();
        if ($row !== false) {
            return $row['maxID'];
        }

        return 0;
    }
}

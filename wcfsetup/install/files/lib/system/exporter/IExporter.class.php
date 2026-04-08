<?php

namespace wcf\system\exporter;

/**
 * Basic interface for all exporters.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IExporter
{
    /**
     * Sets database access data.
     *
     * @param array<string, mixed> $additionalData
     * @return void
     */
    public function setData(
        string $databaseHost,
        string $databaseUser,
        string $databasePassword,
        string $databaseName,
        string $databasePrefix,
        string $fileSystemPath,
        array $additionalData
    );

    /**
     * Initializes this exporter.
     *
     * @return void
     */
    public function init();

    /**
     * Counts the number of required loops for given type.
     *
     * @return int
     */
    public function countLoops(string $objectType);

    /**
     * Runs the data export.
     *
     * @return void
     */
    public function exportData(string $objectType, int $loopCount = 0);

    /**
     * Validates database access.
     *
     * @return void
     * @throws \wcf\system\database\exception\DatabaseException
     */
    public function validateDatabaseAccess();

    /**
     * Validates given file system path. Returns false on failure.
     *
     * @return bool
     */
    public function validateFileAccess();

    /**
     * Validates the selected data types. Returns false on failure.
     *
     * @param string[] $selectedData
     * @return bool
     */
    public function validateSelectedData(array $selectedData);

    /**
     * Returns the import worker queue.
     *
     * @return string[]
     */
    public function getQueue();

    /**
     * Returns the supported data types.
     *
     * @return array<string, string[]>
     */
    public function getSupportedData();

    /**
     * Returns a default database table prefix.
     *
     * @return string
     */
    public function getDefaultDatabasePrefix();
}

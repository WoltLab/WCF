<?php

namespace wcf\system\database;

use wcf\system\database\statement\PreparedStatement;
use wcf\system\exception\SystemException;

/**
 * DatabaseException is a specific SystemException for database errors.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database
 * @deprecated  3.0 - Use \wcf\system\database\exception\DatabaseException
 */
class DatabaseException extends SystemException
{
    /**
     * error number
     * @var int
     */
    protected $errorNumber;

    /**
     * error description
     * @var string
     */
    protected $errorDesc;

    /**
     * sql version number
     * @var string
     */
    protected $sqlVersion;

    /**
     * sql type
     * @var string
     */
    protected $DBType;

    /**
     * database object
     * @var Database
     */
    protected $db;

    /**
     * prepared statement object
     * @var PreparedStatement
     */
    protected $preparedStatement;

    /**
     * SQL query if prepare() failed
     * @var string
     */
    protected $sqlQuery;

    /**
     * Creates a new DatabaseException.
     *
     * @param   string          $message        error message
     * @param   Database        $db         affected db object
     * @param   PreparedStatement   $preparedStatement  affected prepared statement
     * @param   string          $sqlQuery       SQL query if prepare() failed
     */
    public function __construct($message, Database $db, ?PreparedStatement $preparedStatement = null, $sqlQuery = null)
    {
        $this->db = $db;
        $this->DBType = $db->getDBType();
        $this->preparedStatement = $preparedStatement;
        $this->sqlQuery = $sqlQuery;

        // prefer errors from prepared statement
        if ($this->preparedStatement !== null && $this->preparedStatement->getErrorNumber()) {
            $this->errorNumber = $this->preparedStatement->getErrorNumber();
            $this->errorDesc = $this->preparedStatement->getErrorDesc();
        } else {
            $this->errorNumber = $this->db->getErrorNumber();
            $this->errorDesc = $this->db->getErrorDesc();
        }

        parent::__construct($message, \intval($this->errorNumber));
    }

    /**
     * Returns the error number of this exception.
     *
     * @return  int
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }

    /**
     * Returns the error description of this exception.
     *
     * @return  string
     */
    public function getErrorDesc()
    {
        return $this->errorDesc;
    }

    /**
     * Returns the current sql version of the database.
     *
     * @return  string
     */
    public function getSQLVersion()
    {
        if ($this->sqlVersion === null) {
            try {
                $this->sqlVersion = $this->db->getVersion();
            } catch (DatabaseException $e) {
                $this->sqlVersion = 'unknown';
            }
        }

        return $this->sqlVersion;
    }

    /**
     * Returns the sql type of the active database.
     *
     * @return  string
     */
    public function getDBType()
    {
        return $this->DBType;
    }
}

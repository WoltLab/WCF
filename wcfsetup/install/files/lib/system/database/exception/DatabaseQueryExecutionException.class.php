<?php

namespace wcf\system\database\exception;

use wcf\system\database\statement\PreparedStatement;
use wcf\system\exception\IExtraInformationException;
use wcf\util\StringUtil;

/**
 * Denotes an error that is related to a specific database query.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @method mixed|int getCode()
 * @phpstan-import-type ParameterValues from PreparedStatement
 */
class DatabaseQueryExecutionException extends DatabaseQueryException implements IExtraInformationException
{
    /**
     * Parameters that were passed to execute().
     * @var ParameterValues
     */
    protected $parameters = [];

    /**
     * @var ?string
     * @since 5.3
     */
    protected $sqlState;

    /**
     * @var ?string
     * @since 5.3
     */
    protected $driverCode;

    /**
     * @param string $message
     * @param ParameterValues $parameters
     */
    public function __construct($message, $parameters, ?\PDOException $previous = null)
    {
        parent::__construct($message, $previous);

        $this->parameters = $parameters;
        if ($previous) {
            $errorInfo = $previous->errorInfo;
            $this->sqlState = $errorInfo[0] ?? null;
            $this->driverCode = $errorInfo[1] ?? null;
        }
    }

    /**
     * Returns the ANSI SQLSTATE or null.
     *
     * @return ?string
     * @since 5.3
     */
    public function getSqlState()
    {
        return $this->sqlState;
    }

    /**
     * Returns the driver specific error code or null.
     *
     * @return ?string
     * @since 5.3
     */
    public function getDriverCode()
    {
        return $this->driverCode;
    }

    /**
     * Returns the parameters that were passed to execute().
     *
     * @return ParameterValues
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function getExtraInformation()
    {
        $i = 0;

        return \array_map(static function ($val) use (&$i) {
            switch (\gettype($val)) {
                case 'NULL':
                    $val = 'null';
                    break;
                case 'string':
                    $val = "'" . \addcslashes(StringUtil::encodeHTML($val), "\\'") . "'";
                    break;
                case 'boolean':
                    $val = $val ? 'true' : 'false';
                    break;
            }

            return ['Query Parameter ' . (++$i), $val];
        }, $this->getParameters());
    }
}

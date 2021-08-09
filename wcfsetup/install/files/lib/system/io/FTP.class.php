<?php

namespace wcf\system\io;

use wcf\system\exception\SystemException;

/**
 * @deprecated 5.5 - This class was used within the package system in WCF 1. It is no longer in use, a slim wrapper around PHP's FTP extension. Use the extension directly.
 */
class FTP
{
    /**
     * file pointer resource
     * @var resource
     */
    protected $resource;

    /**
     * Opens a new ftp connection to given host.
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @throws  SystemException
     */
    public function __construct($host = 'localhost', $port = 21, $timeout = 30)
    {
        $this->resource = \ftp_connect($host, $port, $timeout);
        if ($this->resource === false) {
            throw new SystemException('Can not connect to ' . $host);
        }
    }

    /**
     * Calls the specified function on the open ftp connection.
     *
     * @param string $function
     * @param array $arguments
     * @return  mixed
     * @throws  SystemException
     */
    public function __call($function, $arguments)
    {
        \array_unshift($arguments, $this->resource);
        if (!\function_exists('ftp_' . $function)) {
            throw new SystemException('Can not call method ' . $function);
        }

        return \call_user_func_array('ftp_' . $function, $arguments);
    }
}

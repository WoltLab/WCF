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
     * @var \FTP\Connection
     */
    protected $resource;

    /**
     * Opens a new ftp connection to given host.
     *
     * @throws  SystemException
     */
    public function __construct(string $host = 'localhost', int $port = 21, int $timeout = 30)
    {
        $resource = \ftp_connect($host, $port, $timeout);
        if ($resource === false) {
            throw new SystemException('Can not connect to ' . $host);
        }

        $this->resource = $resource;
    }

    /**
     * Calls the specified function on the open ftp connection.
     *
     * @param mixed[] $arguments
     * @return  mixed
     * @throws  SystemException
     */
    public function __call(string $function, array $arguments)
    {
        \array_unshift($arguments, $this->resource);
        if (!\function_exists('ftp_' . $function)) {
            throw new SystemException('Can not call method ' . $function);
        }

        return \call_user_func_array('ftp_' . $function, $arguments);
    }
}

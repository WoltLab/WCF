<?php

namespace wcf\system\io;

use wcf\system\exception\SystemException;

/**
 * The File class handles all file operations.
 *
 * Example:
 * using php functions:
 * $fp = fopen('filename', 'wb');
 * fwrite($fp, '...');
 * fclose($fp);
 *
 * using this class:
 * $file = new File('filename');
 * $file->write('...');
 * $file->close();
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method bool     close()
 * @method bool     eof()
 * @method int      filesize()
 * @method bool     flush()
 * @method string|false   gets($length = null)
 * @method bool     lock(int $operation, int &$would_block = null)
 * @method resource open($mode, $use_include_path = false, $context = null)
 * @method int      puts($string, $length = null)       alias of `write`
 * @method string|false   read($length)
 * @method int      seek($offset, $whence = 0)
 * @method array<string, mixed> stat()
 * @method bool     sync()
 * @method int      tell()
 * @method bool     touch($time = 0, $atime = 0)        note: default value of `$time` actually is `time()`
 * @method int      write($string, $length = null)
 */
class File
{
    /**
     * file pointer resource
     * @var resource
     */
    protected $resource;

    /**
     * filename
     * @var string
     */
    protected $filename = '';

    /**
     * Opens a new file.
     *
     * @param array<string, mixed> $options
     * @throws  SystemException
     */
    public function __construct(string $filename, string $mode = 'wb', array $options = [])
    {
        $this->filename = $filename;
        if ($options !== []) {
            $context = \stream_context_create($options);
            $this->resource = \fopen($filename, $mode, false, $context);
        } else {
            $this->resource = \fopen($filename, $mode);
        }
        if ($this->resource === false) {
            throw new SystemException('Can not open file ' . $filename);
        }
    }

    /**
     * Calls the specified function on the open file.
     * Do not call this function directly. Use $file->write('') instead.
     *
     * @param mixed[] $arguments
     * @return  mixed
     * @throws  SystemException
     */
    public function __call(string $function, array $arguments)
    {
        if (\function_exists('f' . $function)) {
            \array_unshift($arguments, $this->resource);

            return \call_user_func_array('f' . $function, $arguments);
        } elseif (\function_exists($function)) {
            \array_unshift($arguments, $this->filename);

            return \call_user_func_array($function, $arguments);
        } else {
            throw new SystemException('Can not call file method ' . $function);
        }
    }
}

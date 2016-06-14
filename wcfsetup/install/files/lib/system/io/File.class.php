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
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
 * 
 * @method	boolean		close()
 * @method	boolean		eof()
 * @method	integer		filesize()
 * @method	string		gets($length = null)
 * @method	resource	open($mode, $use_include_path = false, $context = null)
 * @method	string		read($length)
 * @method	integer		seek($offset, $whence = SEEK_SET)
 * @method	array		stat()
 * @method	integer		tell()
 * @method	boolean		touch($time = 0, $atime = 0)		note: default value of `$time` actually is `time()`
 * @method	integer		write($string, $length = null)
 */
class File {
	/**
	 * file pointer resource
	 * @var	resource
	 */
	protected $resource = null;
	
	/**
	 * filename
	 * @var	string
	 */
	protected $filename = '';
	
	/**
	 * Opens a new file.
	 * 
	 * @param	string		$filename
	 * @param	string		$mode
	 * @param	array		$options
	 * @throws	SystemException
	 */
	public function __construct($filename, $mode = 'wb', $options = []) {
		$this->filename = $filename;
		if (!empty($options)) {
			$context = stream_context_create($options);
			$this->resource = fopen($filename, $mode, false, $context);
		}
		else {
			$this->resource = fopen($filename, $mode);
		}
		if ($this->resource === false) {
			throw new SystemException('Can not open file ' . $filename);
		}
	}
	
	/**
	 * Calls the specified function on the open file.
	 * Do not call this function directly. Use $file->write('') instead.
	 * 
	 * @param	string		$function
	 * @param	array		$arguments
	 * @return	mixed
	 * @throws	SystemException
	 */
	public function __call($function, $arguments) {
		if (function_exists('f' . $function)) {
			array_unshift($arguments, $this->resource);
			return call_user_func_array('f' . $function, $arguments);
		}
		else if (function_exists($function)) {
			array_unshift($arguments, $this->filename);
			return call_user_func_array($function, $arguments);
		}
		else {
			throw new SystemException('Can not call file method ' . $function);
		}
	}
}

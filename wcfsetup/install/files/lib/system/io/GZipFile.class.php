<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The File class handles all file operations on a gzip file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
 * 
 * @method	resource	open($mode, $use_include_path = 0)
 * @method	boolean		rewind()
 */
class GZipFile extends File {
	/**
	 * checks if gz*64 functions are available instead of gz*
	 * https://bugs.php.net/bug.php?id=53829
	 * @var	boolean
	 */
	protected static $gzopen64 = null;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Opens a gzip file.
	 * 
	 * @param	string		$filename
	 * @param	string		$mode
	 * @throws	SystemException
	 */
	public function __construct($filename, $mode = 'wb') {
		if (self::$gzopen64 === null) {
			self::$gzopen64 = (function_exists('gzopen64'));
		}
		
		$this->filename = $filename;
		$this->resource = (self::$gzopen64 ? gzopen64($filename, $mode) : gzopen($filename, $mode));
		if ($this->resource === false) {
			throw new SystemException('Can not open file ' . $filename);
		}
	}
	
	/**
	 * Calls the specified function on the open file.
	 * 
	 * @param	string		$function
	 * @param	array		$arguments
	 * @return	mixed
	 * @throws	SystemException
	 */
	public function __call($function, $arguments) {
		if (self::$gzopen64 && function_exists('gz' . $function . '64')) {
			array_unshift($arguments, $this->resource);
			return call_user_func_array('gz' . $function . '64', $arguments);
		}
		else if (function_exists('gz' . $function)) {
			array_unshift($arguments, $this->resource);
			return call_user_func_array('gz' . $function, $arguments);
		}
		else if (function_exists($function)) {
			array_unshift($arguments, $this->filename);
			return call_user_func_array($function, $arguments);
		}
		else {
			throw new SystemException('Can not call method ' . $function);
		}
	}
	
	/**
	 * Returns the filesize of the unzipped file.
	 * 
	 * @return	integer
	 */
	public function getFileSize() {
		$byteBlock = 1<<14;
		$eof = $byteBlock;
		
		// the correction is for zip files that are too small
		// to get in the first while loop
		$correction = 1;
		while ($this->seek($eof) == 0) {
			$eof += $byteBlock;
			$correction = 0;
		}
		
		while ($byteBlock > 1) {
			$byteBlock >>= 1;
			$eof += $byteBlock * ($this->seek($eof) ? -1 : 1);
		}
		
		if ($this->seek($eof) == -1) $eof--;
		
		$this->rewind();
		return $eof - $correction;
	}
}

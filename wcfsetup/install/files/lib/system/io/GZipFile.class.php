<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;

/**
 * The File class handles all file operations on a gzip file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category	Community Framework
 */
class GZipFile extends File {
	/**
	 * Opens a gzip file.
	 * 
	 * @param	string		$filename
	 * @param	string		$mode
	 */
	public function __construct($filename, $mode = 'wb') {
		$this->filename = $filename;
		$this->resource = gzopen($filename, $mode);
		if ($this->resource === false) {
			throw new SystemException('Can not open file ' . $filename);
		}
	}
	
	/**
	 * Calls the specified function on the open file.
	 * 
	 * @param	string		$function
	 * @param	array		$arguments
	 */
	public function __call($function, $arguments) {
		if (function_exists('gz' . $function)) {
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
		
		if ($this->seek($eof) == -1) $eof -= 1;
				
		$this->rewind();
		return $eof - $correction;
	}
}

<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\GZipFile;
use wcf\system\io\RemoteFile;
use wcf\system\WCF;

/**
 * Contains file-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class FileUtil {
	/**
	 * finfo instance
	 * @var	\finfo
	 */
	protected static $finfo = null;
	
	/**
	 * Tries to find the temp folder.
	 * 
	 * @return	string
	 */
	public static function getTempFolder() {
		// use tmp folder in document root by default
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			if (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false) {
				// strato bugfix
				// create tmp folder in document root automatically
				if (!@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp')) { 
					@mkdir($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
					@chmod($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
				}
			}
			if (@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp') && @is_writable($_SERVER['DOCUMENT_ROOT'].'/tmp')) {
				return $_SERVER['DOCUMENT_ROOT'].'/tmp/';
			}
		}
		
		if (isset($_ENV['TMP']) && @is_writable($_ENV['TMP'])) {
			return $_ENV['TMP'] . '/';
		}
		if (isset($_ENV['TEMP']) && @is_writable($_ENV['TEMP'])) {
			return $_ENV['TEMP'] . '/';
		}
		if (isset($_ENV['TMPDIR']) && @is_writable($_ENV['TMPDIR'])) {
			return $_ENV['TMPDIR'] . '/';
		}
		
		if (($path = ini_get('upload_tmp_dir')) && @is_writable($path)) {
			return $path . '/';
		}
		if (@file_exists('/tmp/') && @is_writable('/tmp/')) {
			return '/tmp/';
		}
		if (function_exists('session_save_path') && ($path = session_save_path()) && @is_writable($path)) {
			return $path . '/';
		}
		
		$path = WCF_DIR.'tmp/';
		if (@file_exists($path) && @is_writable($path)) {
			return $path;
		}
		else {
			if (ini_get('safe_mode')) $reason = "due to php safe_mode restrictions";
			else $reason = "due to an unknown reason";
			throw new SystemException('There is no access to the system temporary folder '.$reason.' and no user specific temporary folder exists in '.WCF_DIR.'! This is a misconfiguration of your webserver software! Please create a folder called '.$path.' using your favorite ftp program, make it writable and then retry this installation.');
		}
	}
	
	/** 
	 * Generates a new temporary filename in TMP_DIR.
	 * 
	 * @param	string		$prefix
	 * @param	string		$extension
	 * @param	string		$dir
	 * @return	string
	 */
	public static function getTemporaryFilename($prefix = 'tmpFile_', $extension = '', $dir = TMP_DIR) {
		$dir = self::addTrailingSlash($dir);
		do {
			$tmpFile = $dir.$prefix.StringUtil::getRandomID().$extension;
		}
		while (file_exists($tmpFile));
		
		return $tmpFile;
	}
	
	/**
	 * Removes a leading slash from the given path.
	 *
	 * @param	string		$path
	 * @return	string
	 */
	public static function removeLeadingSlash($path) {
		return ltrim($path, '/');
	}
	
	/**
	 * Removes a trailing slash from the given path.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	public static function removeTrailingSlash($path) {
		return rtrim($path, '/');
	}
	
	/**
	 * Adds a trailing slash to the given path.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	public static function addTrailingSlash($path) {
		return rtrim($path, '/').'/';
	}
	
	/**
	 * Adds a leading slash to the given path.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	public static function addLeadingSlash($path) {
		return '/'.ltrim($path, '/');
	}
	
	/**
	 * Returns the relative path from the given absolute paths.
	 * 
	 * @param	string		$currentDir
	 * @param	string		$targetDir
	 * @return	string
	 */
	public static function getRelativePath($currentDir, $targetDir) {
		// remove trailing slashes
		$currentDir = self::removeTrailingSlash(self::unifyDirSeperator($currentDir));
		$targetDir = self::removeTrailingSlash(self::unifyDirSeperator($targetDir));
		
		if ($currentDir == $targetDir) {
			return './';
		}
		
		$current = explode('/', $currentDir);
		$target = explode('/', $targetDir);
		
		$relPath = '';
		//for ($i = max(count($current), count($target)) - 1; $i >= 0; $i--) {
		for ($i = 0, $max = max(count($current), count($target)); $i < $max; $i++) {
			if (isset($current[$i]) && isset($target[$i])) {
				if ($current[$i] != $target[$i]) {
					for ($j = 0; $j < $i; $j++) {
						unset($target[$j]);
					}
					$relPath .= str_repeat('../', count($current) - $i).implode('/', $target).'/';
					for ($j = $i + 1; $j < count($current); $j++) {
						unset($current[$j]);
					}
					break;
				}
			}	
			// go up one level
			else if (isset($current[$i]) && !isset($target[$i])) {
				$relPath .= '../';
			}
			else if (!isset($current[$i]) && isset($target[$i])) {
				$relPath .= $target[$i].'/';
			}
		}
		
		return $relPath;
	}
	
	/**
	 * Creates a path on the local filesystem and returns true on success.
	 * Parent directories do not need to exists as they will be created if
	 * necessary.
	 * 
	 * @param	string		$path
	 * @param	integer		$chmod
	 * @return	boolean
	 */
	public static function makePath($path, $chmod = 0777) {
		// directory already exists, abort
		if (file_exists($path)) {
			return false;
		}
		
		// check if parent directory exists
		$parent = dirname($path);
		if ($parent != $path) {
			// parent directory does not exist either
			// we have to create the parent directory first
			$parent = self::addTrailingSlash($parent);
			if (!@file_exists($parent)) {
				// could not create parent directory either => abort
				if (!self::makePath($parent, $chmod)) {
					return false;
				}
			}
			
			// well, the parent directory exists or has been created
			// lets create this path
			$oldumask = @umask(0);
			if (!@mkdir($path, $chmod)) {
				return false;
			}
			@umask($oldumask);
			/*if (!@chmod($path, $chmod)) {
				return false;
			}*/
			if (self::isApacheModule() || !@is_writable($path)) {
				@chmod($path, 0777);
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Unifies windows and unix directory seperators.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	public static function unifyDirSeperator($path) {
		$path = str_replace('\\\\', '/', $path);
		$path = str_replace('\\', '/', $path);
		return $path;
	}
	
	/**
	 * Scans a folder (and subfolder) for a specific file.
	 * Returns the filename if found, otherwise false.
	 * 
	 * @param	string		$folder
	 * @param	string		$searchfile
	 * @param	boolean		$recursive
	 * @return	mixed
	 */
	public static function scanFolder($folder, $searchfile, $recursive = true) {
		if (!@is_dir($folder)) {
			return false;
		}
		if (!$searchfile) {
			return false;
		}
		
		$folder = self::addTrailingSlash($folder);
		$dirh = @opendir($folder);
		while ($filename = @readdir($dirh)) {
			if ($filename == '.' || $filename == '..') {
				continue;
			}
			if ($filename == $searchfile) {
				@closedir($dirh);
				return $folder.$filename;
			}
			
			if ($recursive == true && @is_dir($folder.$filename)) {
				if ($found = self::scanFolder($folder.$filename, $searchfile, $recursive)) {
					@closedir($dirh);
					return $found;
				}
			}
		}
		@closedir($dirh);
	}
	
	/**
	 * Return true, if the given filename is an url (http or ftp).
	 * 
	 * @param	string		$filename
	 * @return	boolean
	 */
	public static function isURL($filename) {
		return preg_match('!^(https?|ftp)://!', $filename);
	}
	
	/**
	 * Returns canonicalized absolute pathname.
	 * 
	 * @param	string		$path
	 * @return	string		path
	 */
	public static function getRealPath($path) {
		$path = self::unifyDirSeperator($path);
		
		$result = array();
		$pathA = explode('/', $path);
		if ($pathA[0] === '') {
			$result[] = '';
		}
		
		foreach ($pathA as $key => $dir) {
			if ($dir == '..') {
				if (end($result) == '..') {
					$result[] = '..';
				} 
				else { 
					$lastValue = array_pop($result);
					if ($lastValue === '' || $lastValue === null) {
						$result[] = '..';
					}
				}
			} 
			else if ($dir !== '' && $dir != '.') {
				$result[] = $dir;
			}
		}
		
		$lastValue = end($pathA);
		if ($lastValue === '' || $lastValue === false) {
			$result[] = '';
		}
		
		return implode('/', $result);
	}
	
	/**
	 * Formats the given filesize.
	 * 
	 * @param	integer		$byte
	 * @param	integer		$precision
	 * @return	string
	 */
	public static function formatFilesize($byte, $precision = 2) {
		$symbol = 'Byte';
		if ($byte >= 1000) {
			$byte /= 1000;
			$symbol = 'kB';
		}
		if ($byte >= 1000) {
			$byte /= 1000;
			$symbol = 'MB';
		}
		if ($byte >= 1000) {
			$byte /= 1000;
			$symbol = 'GB';
		}
		if ($byte >= 1000) {
			$byte /= 1000;
			$symbol = 'TB';
		}
		
		return StringUtil::formatNumeric(round($byte, $precision)).' '.$symbol;
	}
	
	/**
	 * Formats a filesize with binary prefix.
	 * 
	 * For more informations: <http://en.wikipedia.org/wiki/Binary_prefix>
	 * 
	 * @param	integer		$byte
	 * @param	integer		$precision
	 * @return	string
	 */
	public static function formatFilesizeBinary($byte, $precision = 2) {
		$symbol = 'Byte';
		if ($byte >= 1024) {
			$byte /= 1024;
			$symbol = 'KiB';
		}
		if ($byte >= 1024) {
			$byte /= 1024;
			$symbol = 'MiB';
		}
		if ($byte >= 1024) {
			$byte /= 1024;
			$symbol = 'GiB';
		}
		if ($byte >= 1024) {
			$byte /= 1024;
			$symbol = 'TiB';
		}
		
		return StringUtil::formatNumeric(round($byte, $precision)).' '.$symbol;
	}
	
	/**
	 * Downloads a package archive from an http URL and returns the path to
	 * the downloaded file.
	 * 
	 * @param	string		$httpUrl
	 * @param	string		$prefix
	 * @param	array		$options
	 * @param	array		$postParameters
	 * @param	array		$headers		empty array or a not initialized variable
	 * @return	string
	 * @deprecated	This method currently only is a wrapper around \wcf\util\HTTPUtil. Please use HTTPUtil 
	 * 		from now on, as this method may be removed in the future.
	 */
	public static function downloadFileFromHttp($httpUrl, $prefix = 'package', array $options = array(), array $postParameters = array(), &$headers = array()) {
		$request = new HTTPRequest($httpUrl, $options, $postParameters);
		$request->execute();
		$reply = $request->getReply();
		
		$newFileName = self::getTemporaryFilename($prefix.'_');
		file_put_contents($newFileName, $reply['body']); // the file to write.
		
		$tmp = $reply['headers']; // copy variable, to avoid problems with the reference
		$headers = $tmp;
		
		return $newFileName;
	}
	
	/**
	 * Strips supernumerous BOMs from a given bytestream.
	 *
	 * If we are dealing with bytestreams being pushed from one program or script to another in a UTF-8 
	 * environment, we might encounter problems with BOMs (Byte Order Marks). E.g., if there's a script 
	 * that reads a .tar file via readfile(), and this script is encoded in UTF-8, and being called from another
	 * script which wants to handle the bytestream that results from readfile(). But apparently because of the 
	 * UTF-8 encoding of the called script -- at least in some PHP versions -- readfile() adds a UTF-8 BOM
	 * at the beginning of the bytestream. If we do write this bytestream to disk and then try to open the
	 * resulting file, we will get an error because it is no more a valid .tar archive. The same thing happens
	 * if we handle an .xml file and then try to parse it.
	 * So, if bytestreams are being handled in a UTF-8 environment, be sure always to use this function 
	 * before writing the bytestream to disk or trying to parse it with an xml parser.
	 * This works regardless of multibyte string support (mb_strpos and friends) being enabled or not.
	 * 
	 * Btw, if you try to apply the following to a bytestream read from a .tar file, 
	 * you will end up with a file sized zero bytes:
	 * while (($byte = fgetc($fileHandle)) !== false) {
	 *	fwrite($fileHandle, $byte);
	 * }
	 * 
	 * @param	string		$sourceContent
	 * @param	string		$characterEncoding
	 * @return	string		destinationContent
	 */
	public static function stripBoms($sourceContent = '', $characterEncoding = 'UTF-8') {
		try {
			// TODO: implement recognition of other BOMs (UTF-7, UTF-16 big endian, UTF-16 little endian etc.)
			if ($characterEncoding == 'UTF-8') {
				// get the ASCII codes for the three bytes the UTF-8 BOM is consisting of.
				$firstByte = intval(0xEF);
				$secondByte = intval(0xBB);
				$thirdByte = intval(0xBF);
			}
			else {
				return $sourceContent;
			}
			
			// put the bytestream's first three bytes to an array.
			$workArray = unpack('C3', $sourceContent);
			if (!is_array($workArray)) {
				throw new SystemException("Unable to process bytestream.");
			}
			
			// detect the UTF-8 BOM.
			if (($workArray['1'] == $firstByte) && ($workArray['2'] == $secondByte) && ($workArray['3'] == $thirdByte)) {
				$tmpname = FileUtil::getTemporaryFilename('stripBoms_');
				$tmpStream = fopen($tmpname, 'w+');
				fwrite($tmpStream, $sourceContent);
				rewind($tmpStream);
				
				// cut off the BOM.
				fseek($tmpStream, 3); // compatibility for PHP < 5.1.0
				$destinationContent = stream_get_contents($tmpStream);
				fclose($tmpStream);
				@unlink($tmpname);
				
				return $destinationContent;
			} 
			else {
				return $sourceContent;
			}
		}
		catch (SystemException $e) {
			// clean up
			if (isset($tmpname) && file_exists($tmpname)) @unlink($tmpname);
			
			throw $e;
		}
	}
	
	/**
	 * Determines whether a file is text or binary by checking the first few bytes in the file.
	 * The exact number of bytes is system dependent, but it is typically several thousand.
	 * If every byte in that part of the file is non-null, considers the file to be text;
	 * otherwise it considers the file to be binary.
	 * 
	 * @param	string		$file
	 * @return	boolean
	 */
	public static function isBinary($file) {
		// open file
		$file = new File($file, 'rb');
		
		// get block size
		$stat = $file->stat();
		$blockSize = $stat['blksize'];
		if ($blockSize < 0) $blockSize = 1024;
		if ($blockSize > $file->filesize()) $blockSize = $file->filesize();
		if ($blockSize <= 0) return false;
		
		// get bytes
		$block = $file->read($blockSize);
		return (strlen($block) == 0 || strpos($block, "\0") !== false);
	}
	
	/**
	 * Uncompresses a gzipped file and returns true if successful.
	 * 
	 * @param	string		$gzipped
	 * @param	string		$destination
	 * @return	boolean	
	 */
	public static function uncompressFile($gzipped, $destination) {
		if (!@is_file($gzipped)) {
			return false;
		}
		
		$sourceFile = new GZipFile($gzipped, 'rb');
		//$filesize = $sourceFile->getFileSize();
		$targetFile = new File($destination);
		while (!$sourceFile->eof()) {
			$targetFile->write($sourceFile->read(512), 512);
		}
		$targetFile->close();
		$sourceFile->close();
		@$targetFile->chmod(0777);
		
		/*if ($filesize != filesize($destination)) {
			@unlink($destination);
			return false;
		}*/
		
		return true;
	}
	
	/**
	 * Returns true, if php is running as apache module.
	 * 
	 * @return	boolean
	 */
	public static function isApacheModule() {
		return function_exists('apache_get_version');
	}
	
	/**
	 * Returns the mime type of a file.
	 * 
	 * @param	string		$filename
	 * @return	string
	 */
	public static function getMimeType($filename) {
		if (self::$finfo === null) {
			if (!class_exists('\finfo', false)) return '';
			self::$finfo = new \finfo(FILEINFO_MIME_TYPE);
		}
		
		return self::$finfo->file($filename);
	}
	
	private function __construct() { }
}

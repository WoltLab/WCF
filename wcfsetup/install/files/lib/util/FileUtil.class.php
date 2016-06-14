<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\GZipFile;

/**
 * Contains file-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class FileUtil {
	/**
	 * finfo instance
	 * @var	\finfo
	 */
	protected static $finfo = null;
	
	/**
	 * memory limit in bytes
	 * @var	integer
	 */
	protected static $memoryLimit = null;
	
	/**
	 * chmod mode
	 * @var	string
	 */
	protected static $mode = null;
	
	/**
	 * Tries to find the temp folder.
	 * 
	 * @return	string
	 * @throws	SystemException
	 */
	public static function getTempFolder() {
		try {
			// This method does not contain any shut up operator by intent.
			// Any operation that fails here is fatal.
			$path = WCF_DIR.'tmp/';
			
			if (is_file($path)) {
				// wat
				unlink($path);
			}
			
			if (!file_exists($path)) {
				mkdir($path, 0777);
			}
			
			if (!is_dir($path)) {
				throw new SystemException("Temporary folder '".$path."' does not exist and could not be created. Please check the permissions of the '".WCF_DIR."' folder using your favorite ftp program.");
			}
			
			if (!is_writable($path)) {
				self::makeWritable($path);
			}
			
			if (!is_writable($path)) {
				throw new SystemException("Temporary folder '".$path."' is not writable. Please check the permissions using your favorite ftp program.");
			}
			
			file_put_contents($path.'/.htaccess', 'deny from all');
			
			return $path;
		}
		catch (SystemException $e) {
			// use tmp folder in document root by default
			if (!empty($_SERVER['DOCUMENT_ROOT'])) {
				if (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false) {
					// strato bugfix
					// create tmp folder in document root automatically
					if (!@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp')) {
						@mkdir($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
						self::makeWritable($_SERVER['DOCUMENT_ROOT'].'/tmp/');
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
			
			throw new SystemException('There is no access to the system temporary folder due to an unknown reason and no user specific temporary folder exists in '.WCF_DIR.'! This is a misconfiguration of your webserver software! Please create a folder called '.$path.' using your favorite ftp program, make it writable and then retry this installation.');
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
		$currentDir = self::removeTrailingSlash(self::unifyDirSeparator($currentDir));
		$targetDir = self::removeTrailingSlash(self::unifyDirSeparator($targetDir));
		
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
	 * @return	boolean
	 */
	public static function makePath($path) {
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
				if (!self::makePath($parent)) {
					return false;
				}
			}
			
			// well, the parent directory exists or has been created
			// lets create this path
			if (!@mkdir($path)) {
				return false;
			}
			
			self::makeWritable($path);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Unifies windows and unix directory separators.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	public static function unifyDirSeparator($path) {
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
	 * Returns true if the given filename is an url (http or ftp).
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
		$path = self::unifyDirSeparator($path);
		
		$result = [];
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
	 * @deprecated	This method currently only is a wrapper around \wcf\util\HTTPRequest. Please use
	 * 		HTTPRequest from now on, as this method may be removed in the future.
	 */
	public static function downloadFileFromHttp($httpUrl, $prefix = 'package', array $options = [], array $postParameters = [], &$headers = []) {
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
		
		self::makeWritable($destination);
		
		return true;
	}
	
	/**
	 * Returns true if php is running as apache module.
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
			if (!class_exists('\finfo', false)) return 'application/octet-stream';
			self::$finfo = new \finfo(FILEINFO_MIME_TYPE);
		}
		
		return self::$finfo->file($filename) ?: 'application/octet-stream';
	}
	
	/**
	 * Tries to make a file or directory writable. It starts of with the least
	 * permissions and goes up until 0666 for files and 0777 for directories.
	 * 
	 * @param	string		$filename
	 * @throws	SystemException
	 */
	public static function makeWritable($filename) {
		if (!file_exists($filename)) {
			return;
		}
		
		if (self::$mode === null) {
			// WCFSetup
			if (defined('INSTALL_SCRIPT') && file_exists(INSTALL_SCRIPT)) {
				// do not use PHP_OS here, as this represents the system it was built on != running on
				// php_uname() is forbidden on some strange hosts; PHP_EOL is reliable 
				if (PHP_EOL == "\r\n") {
					// Windows
					self::$mode = '0777';
				}
				else {
					// anything but Windows
					clearstatcache();
					
					self::$mode = '0666';
					
					$tmpFilename = '__permissions_'.sha1(time()).'.txt';
					@touch($tmpFilename);
					
					// create a new file and check the file owner, if it is the same
					// as this file (uploaded through FTP), we can safely grant write
					// permissions exclusively to the owner rather than everyone
					if (file_exists($tmpFilename)) {
						$scriptOwner = fileowner(INSTALL_SCRIPT);
						$fileOwner = fileowner($tmpFilename);
						
						if ($scriptOwner === $fileOwner) {
							self::$mode = '0644';
						}
						
						@unlink($tmpFilename);
					}
				}
			}
			else {
				// mirror permissions of WCF.class.php
				if (!file_exists(WCF_DIR . 'lib/system/WCF.class.php')) {
					throw new SystemException("Unable to find 'wcf/lib/system/WCF.class.php'.");
				}
				
				self::$mode = '0' . substr(sprintf('%o', fileperms(WCF_DIR . 'lib/system/WCF.class.php')), -3);
			}
		}
		
		if (is_dir($filename)) {
			if (self::$mode == '0644') {
				@chmod($filename, 0755);
			}
			else {
				@chmod($filename, 0777);
			}
		}
		else {
			@chmod($filename, octdec(self::$mode));
		}
		
		if (!is_writable($filename)) {
			// does not work with 0777
			throw new SystemException("Unable to make '".$filename."' writable. This is a misconfiguration of your server, please contact your system administrator or hosting provider.");
		}
	}
	
	/**
	 * Returns memory limit in bytes.
	 * 
	 * @return	integer
	 */
	public static function getMemoryLimit() {
		if (self::$memoryLimit === null) {
			self::$memoryLimit = 0;
			
			$memoryLimit = ini_get('memory_limit');
			
			// no limit
			if ($memoryLimit == -1) {
				self::$memoryLimit = -1;
			}
			
			// completely numeric, PHP assumes byte
			if (is_numeric($memoryLimit)) {
				self::$memoryLimit = $memoryLimit;
			}
			
			// PHP supports 'K', 'M' and 'G' shorthand notation
			if (preg_match('~^(\d+)([KMG])$~', $memoryLimit, $matches)) {
				switch ($matches[2]) {
					case 'K':
						self::$memoryLimit = $matches[1] * 1024;
					break;
					
					case 'M':
						self::$memoryLimit = $matches[1] * 1024 * 1024;
					break;
					
					case 'G':
						self::$memoryLimit = $matches[1] * 1024 * 1024 * 1024;
					break;
				}
			}
		}
		
		return self::$memoryLimit;
	}
	
	/**
	 * Returns true if the given amount of memory is available.
	 * 
	 * @param	integer		$neededMemory
	 * @return	boolean
	 */
	public static function checkMemoryLimit($neededMemory) {
		return self::getMemoryLimit() == -1 || self::getMemoryLimit() > (memory_get_usage() + $neededMemory);
	}
	
	/**
	 * Returns the FontAwesome icon CSS class name for a file with the given
	 * mime type.
	 * 
	 * @param	string		$mimeType
	 * @return	string
	 */
	public static function getIconClassByMimeType($mimeType) {
		if (StringUtil::startsWith($mimeType, 'image/')) {
			return 'fa-file-image-o';
		}
		else if (StringUtil::startsWith($mimeType, 'video/')) {
			return 'fa-file-video-o';
		}
		else if (StringUtil::startsWith($mimeType, 'audio/')) {
			return 'fa-file-sound-o';
		}
		else if (StringUtil::startsWith($mimeType, 'text/')) {
			return 'fa-file-text-o';
		}
		else {
			switch ($mimeType) {
				case 'application/msword':
				case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
					return 'fa-file-word-o';
				break;
				
				case 'application/pdf':
					return 'fa-file-pdf-o';
				break;
				
				case 'application/vnd.ms-powerpoint':
				case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
					return 'fa-file-powerpoint-o';
				break;
				
				case 'application/vnd.ms-excel':
				case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
					return 'fa-file-excel-o';
				break;
				
				case 'application/zip':
				case 'application/x-tar':
				case 'application/x-gzip':
					return 'fa-file-archive-o';
				break;
				
				case 'application/xml':
					return 'fa-file-text-o';
				break;
			}
		}
		
		return 'fa-file-o';
	}
	
	/**
	 * Forbid creation of FileUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}

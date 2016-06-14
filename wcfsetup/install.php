<?php
/**
 * This script tries to find the temp folder and unzip all setup files into.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
// @codingStandardsIgnoreFile
// define constants
define('INSTALL_SCRIPT', __FILE__);
define('INSTALL_SCRIPT_DIR', dirname(__FILE__).'/');
define('SETUP_FILE', INSTALL_SCRIPT_DIR . 'WCFSetup.tar.gz');
define('NO_IMPORTS', 1);

// set exception handler
set_exception_handler('handleException');
// set php error handler
set_error_handler('handleError', E_ALL);

// define list of needed file
$neededFilesPattern = [
	'!^setup/.*!',
	'!^install/files/acp/images/wcfLogo.*!',
	'!^install/files/acp/style/setup/.*!',
	'!^install/files/lib/data/.*!',
	'!^install/files/icon/.*!',
	'!^install/files/font/.*!',
	'!^install/files/lib/system/.*!',
	'!^install/files/lib/util/.*!',
	'!^install/lang/.*!',
	'!^install/packages/.*!'];
	
// define needed functions and classes
/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * WCF::handleException() calls the show method on exceptions that implement this interface.
 *
 * @package	com.woltlab.wcf
 * @author	Marcel Werk
 */
interface IPrintableException {
	public function show();
}

// define needed classes
// needed are:
// SystemException, PrintableException, BasicFileUtil, Tar, File, ZipFile
/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * A SystemException is thrown when an unexpected error occurs.
 *
 * @package	com.woltlab.wcf
 * @author	Marcel Werk
 */
class SystemException extends \Exception implements IPrintableException {
	protected $description;
	protected $information = '';
	protected $functions = '';
	
	/**
	 * Creates a new SystemException.
	 *
	 * @param	string		$message	error message
	 * @param	integer		$code		error code
	 * @param	string		$description	description of the error
	 */
	public function __construct($message = '', $code = 0, $description = '') {
		parent::__construct($message, $code);
		$this->description = $description;
	}
	
	/**
	 * Returns the description of this exception.
	 *
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Prints this exception.
	 * This method is called by WCF::handleException().
	 */
	public function show() {
		?>
<html>
<head>
<title>Fatal error: <?php echo htmlspecialchars($this->getMessage()); ?></title>

<style type="text/css">
	body {
		font-family: Verdana, Helvetica, sans-serif;
		font-size: 0.8em;
	}
	div {
		border: 1px outset lightgrey;
		padding: 3px;
		background-color: lightgrey;
	}
	
	div div {
		border: 1px inset lightgrey;
		padding: 4px;
	}
	
	h1 {
		background-color: #154268;
		padding: 4px;
		color: #fff;
		margin: 0 0 3px 0;
		font-size: 1.15em;
	}
	h2 {
		font-size: 1.1em;
		margin-bottom: 0;
	}
	
	pre, p {
		margin: 0;
	}
</style>
</head>

<body>
	<div>
		<h1>Fatal error: <?php echo htmlspecialchars($this->getMessage()); ?></h1>
		
		<div>
			<p><?php echo $this->getDescription(); ?></p>
			<?php if ($this->getCode()) { ?><p>You get more information about the problem in our knowledge base: <a href="http://www.woltlab.com/help/?code=<?php echo intval($this->getCode()); ?>">http://www.woltlab.com/help/?code=<?php echo intval($this->getCode()); ?></a></p><?php } ?>
			
			<h2>Information:</h2>
			<p>
				<b>error message:</b> <?php echo htmlspecialchars($this->getMessage()); ?><br>
				<b>error code:</b> <?php echo intval($this->getCode()); ?><br>
				<?php echo $this->information; ?>
				<b>file:</b> <?php echo htmlspecialchars($this->getFile()); ?> (<?php echo $this->getLine(); ?>)<br>
				<b>php version:</b> <?php echo htmlspecialchars(phpversion()); ?><br>
				<b>wcf version:</b> <?php if (defined('WCF_VERSION')) echo WCF_VERSION; ?><br>
				<b>date:</b> <?php echo gmdate('r'); ?><br>
				<b>request:</b> <?php if (isset($_SERVER['REQUEST_URI'])) echo htmlspecialchars($_SERVER['REQUEST_URI']); ?><br>
				<b>referer:</b> <?php if (isset($_SERVER['HTTP_REFERER'])) echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?><br>
			</p>
			
			<h2>Stacktrace:</h2>
			<pre><?php echo htmlspecialchars($this->getTraceAsString()); ?></pre>
		</div>
		
		<?php echo $this->functions; ?>
	</div>
</body>
</html>

<?php
	}
}

/**
 * Loads the required classes automatically.
 */
spl_autoload_register(function($className) {
	$namespaces = explode('\\', $className);
	if (count($namespaces) > 1) {
		// remove 'wcf' component
		array_shift($namespaces);
		
		$className = implode('/', $namespaces);
		$classPath = TMP_DIR . 'install/files/lib/' . $className . '.class.php';
		if (file_exists($classPath)) {
			require_once($classPath);
		}
	}
});

/**
 * Escapes strings for execution in sql queries.
 * 
 * @param	string		$string
 * @return	string
 */
function escapeString($string) {
	return \wcf\system\WCF::getDB()->escapeString($string);
}

/**
 * Calls the show method on the given exception.
 *
 * @param	mixed	$e
 */
function handleException($e) {
	try {
		if (!($e instanceof \Exception)) throw $e;
		
		if ($e instanceof IPrintableException || $e instanceof \wcf\system\exception\IPrintableException) {
			$e->show();
			exit;
		}
	}
	catch (\Throwable $exception) {
		die("<pre>WCF::handleException() Unhandled exception: ".$exception->getMessage()."\n\n".$exception->getTraceAsString());
	}
	catch (\Exception $exception) {
		die("<pre>WCF::handleException() Unhandled exception: ".$exception->getMessage()."\n\n".$exception->getTraceAsString());
	}
}

/**
 * Catches php errors and throws instead a system exception.
 *
 * @param	integer		$errorNo
 * @param	string		$message
 * @param	string		$filename
 * @param	integer		$lineNo
 * @throws	SystemException
 */
function handleError($errorNo, $message, $filename, $lineNo) {
	if (error_reporting() != 0) {
		$type = 'error';
		switch ($errorNo) {
			case 2: $type = 'warning';
				break;
			case 8: $type = 'notice';
				break;
		}
		
		throw new SystemException('PHP '.$type.' in file '.$filename.' ('.$lineNo.'): '.$message, 0);
	}
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * BasicFileUtil contains file-related functions.
 *
 * @package	com.woltlab.wcf
 * @author	Marcel Werk
 */
class BasicFileUtil {
	/**
	 * chmod mode
	 * @var	integer
	 */
	protected static $mode = null;
	
	/**
	 * Tries to find the temp folder.
	 *
	 * @return	string
	 * @throws	SystemException
	 */
	public static function getTempFolder() {
		// use tmp folder in document root by default
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			if (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false) {
				// strato bugfix
				// create tmp folder in document root automatically
				if (!@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp')) {
					@mkdir($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
					try {
						self::makeWritable($_SERVER['DOCUMENT_ROOT'].'/tmp/');
					}
					catch (SystemException $e) {}
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
		
		$path = INSTALL_SCRIPT_DIR.'tmp/';
		if (@file_exists($path) && @is_writable($path)) {
			return $path;
		}
		else {
			throw new SystemException('There is no access to the system temporary folder due to an unknown reason and no user specific temporary folder exists in '.INSTALL_SCRIPT_DIR.'! This is a misconfiguration of your webserver software! Please create a folder called '.$path.' using your favorite ftp program, make it writable and then retry this installation.');
		}
	}
	
	/**
	 * Returns the temp folder for the installation.
	 *
	 * @return	string
	 */
	public static function getInstallTempFolder() {
		$dir = self::getTempFolder() . TMP_FILE_PREFIX . '/';
		@mkdir($dir);
		self::makeWritable($dir);
		
		return $dir;
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
		
		// determine mode
		if (self::$mode === null) {
			// do not use PHP_OS here, as this represents the system it was built on != running on
			// php_uname() is forbidden on some strange hosts; PHP_EOL is reliable 
			if (PHP_EOL == "\r\n") {
				// Windows
				self::$mode = 0777;
			}
			else {
				// anything but Windows
				clearstatcache();
				
				self::$mode = 0666;
				
				$tmpFilename = '__permissions_'.sha1(time()).'.txt';
				@touch($tmpFilename);
				
				// create a new file and check the file owner, if it is the same
				// as this file (uploaded through FTP), we can safely grant write
				// permissions exclusively to the owner rather than everyone
				if (file_exists($tmpFilename)) {
					$scriptOwner = fileowner(__FILE__);
					$fileOwner = fileowner($tmpFilename);
					
					if ($scriptOwner === $fileOwner) {
						self::$mode = 0644;
					}
					
					@unlink($tmpFilename);
				}
			}
		}
		
		if (is_dir($filename)) {
			if (self::$mode == 0644) {
				@chmod($filename, 0755);
			}
			else {
				@chmod($filename, 0777);
			}
		}
		else {
			@chmod($filename, self::$mode);
		}
		
		if (!is_writable($filename)) {
			throw new SystemException("Unable to make '".$filename."' writable. This is a misconfiguration of your server, please contact your system administrator or hosting provider.");
		}
	}
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * Opens tar or tar.gz archives.
 *
 * Usage:
 * ------
 * $tar = new Tar('archive.tar');
 * $contentList = $tar->getContentList();
 * foreach ($contentList as $key => $val) {
 * 	$tar->extract($key, DESTINATION);
 * }
 */
class Tar {
	protected $archiveName = '';
	protected $contentList = [];
	protected $opened = false;
	protected $read = false;
	protected $file = null;
	protected $isZipped = false;
	protected $mode = 'rb';
	
	/**
	 * Creates a new Tar object.
	 * archiveName must be tarball or gzipped tarball
	 *
	 * @param	string		$archiveName
	 * @throws	SystemException
	 */
	public function __construct($archiveName) {
		if (!is_file($archiveName)) {
			throw new SystemException("unable to find tar archive '".$archiveName."'");
		}
		
		$this->archiveName = $archiveName;
		$this->open();
		$this->readContent();
	}
	
	/**
	 * Destructor of this class, closes tar archive.
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * Opens the tar archive and stores filehandle.
	 */
	public function open() {
		if (!$this->opened) {
			if ($this->isZipped) $this->file = new ZipFile($this->archiveName, $this->mode);
			else {
				// test compression
				$this->file = new File($this->archiveName, $this->mode);
				if ($this->file->read(2) == "\37\213") {
					$this->file->close();
					$this->isZipped = true;
					$this->file = new ZipFile($this->archiveName, $this->mode);
				}
				else {
					$this->file->seek(0);
				}
			}
			$this->opened = true;
		}
	}

	/**
	 * Closes the opened file.
	 */
	public function close() {
		if ($this->opened) {
			$this->file->close();
			$this->opened = false;
		}
	}
	
	/**
	 * Returns the table of contents (TOC) list for this tar archive.
	 *
	 * @return	array		list of content
	 */
	public function getContentList() {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		return $this->contentList;
	}
	
	/**
	 * Returns an associative array with information
	 * about a specific file in the archive.
	 *
	 * @param	mixed	$fileIndex	index or name of the requested file
	 * @return	array
	 * @throws	SystemException
	 */
	public function getFileInfo($fileIndex) {
		if (!is_int($fileIndex)) {
			$fileIndex = $this->getIndexByFilename($fileIndex);
		}
		
		if (!isset($this->contentList[$fileIndex])) {
			throw new SystemException("Tar: could find file '".$fileIndex."' in archive");
		}
		return $this->contentList[$fileIndex];
	}
	
	/**
	 * Searchs a file in the tar archive
	 * and returns the numeric fileindex.
	 * Returns false if not found.
	 *
	 * @param	string		$filename
	 * @return	integer			index of the requested file
	 */
	public function getIndexByFilename($filename) {
		foreach ($this->contentList as $index => $file) {
			if ($file['filename'] == $filename) {
				return $index;
			}
		}
		return false;
	}
	
	/**
	 * Extracts a specific file and returns the content as string.
	 * Returns false if extraction failed.
	 *
	 * @param	mixed		$index		index or name of the requested file
	 * @return	string				content of the requested file
	 */
	public function extractToString($index) {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);
		
		// can not extract a folder
		if ($header['type'] != 'file') {
			return false;
		}
		
		// seek to offset
		$this->file->seek($header['offset']);
		
		// read data
		$content = '';
		$n = floor($header['size'] / 512);
		for($i = 0; $i < $n; $i++) {
			$content .= $this->file->read(512);
		}
		if(($header['size'] % 512) != 0) {
			$buffer = $this->file->read(512);
			$content .= substr($buffer, 0, ($header['size'] % 512));
		}
		
		return $content;
	}
	
	/**
	 * Extracts a specific file and writes it's content
	 * to the file specified with $destination.
	 *
	 * @param	mixed		$index		index or name of the requested file
	 * @param	string		$destination
	 * @return	boolean
	 * @throws	SystemException
	 */
	public function extract($index, $destination) {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);
		
		// can not extract a folder
		if ($header['type'] != 'file') {
			return false;
		}
		
		// seek to offset
		$this->file->seek($header['offset']);
		
		$targetFile = new File($destination);
		
		// read data
		$n = floor($header['size'] / 512);
		for ($i = 0; $i < $n; $i++) {
			$content = $this->file->read(512);
			$targetFile->write($content, 512);
		}
		if (($header['size'] % 512) != 0) {
			$content = $this->file->read(512);
			$targetFile->write($content, ($header['size'] % 512));
		}
		
		$targetFile->close();
		BasicFileUtil::makeWritable($destination);
		
		if ($header['mtime']) {
			@$targetFile->touch($header['mtime']);
		}
		
		// check filesize
		if (filesize($destination) != $header['size']) {
			throw new SystemException("Could not untar file '".$header['filename']."' to '".$destination."'. Maybe disk quota exceeded in folder '".dirname($destination)."'.");
		}
		
		return true;
	}
	
	/**
	 * Reads table of contents (TOC) from tar archive.
	 * This does not get the entire to memory but only parts of it.
	 */
	protected function readContent() {
		$this->contentList = [];
		$this->read = true;
		$i = 0;
		
		// Read the 512 bytes header
		$longFilename = null;
		while (strlen($binaryData = $this->file->read(512)) != 0) {
			// read header
			$header = $this->readHeader($binaryData);
			if ($header === false) {
				continue;
			}
			
			// fixes a bug that files with long names aren't correctly
			// extracted
			if ($longFilename !== null) {
				$header['filename'] = $longFilename;
				$longFilename = null;
			}
			if ($header['typeflag'] == 'L') {
				$format = 'Z'.$header['size'].'filename';
				
				$fileData = unpack($format, $this->file->read(512));
				$longFilename = $fileData['filename'];
				$header['size'] = 0;
			}
			// don't include the @LongLink file in the content list
			else {
				$this->contentList[$i] = $header;
				$this->contentList[$i]['index'] = $i;
				$i++;
			}
			
			$this->file->seek($this->file->tell() + (512 * ceil(($header['size'] / 512))));
		}
	}
	
	/**
	 * Unpacks file header for one file entry.
	 * 
	 * @param	string		$binaryData
	 * @return	array		$fileheader
	 */
	protected function readHeader($binaryData) {
		if (strlen($binaryData) != 512) {
			return false;
		}
		
		$header = [];
		$checksum = 0;
		// First part of the header
		for ($i = 0; $i < 148; $i++) {
			$checksum += ord(substr($binaryData, $i, 1));
		}
		// Calculate the checksum
		// Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++) {
			$checksum += ord(' ');
		}
		// Last part of the header
		for ($i = 156; $i < 512; $i++) {
			$checksum += ord(substr($binaryData, $i, 1));
		}
		
		// extract values
		$format = 'Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Z1typeflag/Z100link/Z6magic/Z2version/Z32uname/Z32gname/Z8devmajor/Z8devminor/Z155prefix';
		
		$data = unpack($format, $binaryData);
		
		// Extract the properties
		$header['checksum'] = octdec(trim($data['checksum']));
		if ($header['checksum'] == $checksum) {
			$header['filename'] = trim($data['filename']);
			$header['mode'] = octdec(trim($data['mode']));
			$header['uid'] = octdec(trim($data['uid']));
			$header['gid'] = octdec(trim($data['gid']));
			$header['size'] = octdec(trim($data['size']));
			$header['mtime'] = octdec(trim($data['mtime']));
			$header['prefix'] = trim($data['prefix']);
			if ($header['prefix']) {
				$header['filename'] = $header['prefix'].'/'.$header['filename'];
			}
			if (($header['typeflag'] = $data['typeflag']) == '5') {
				$header['size'] = 0;
				$header['type'] = 'folder';
			}
			else {
				$header['type'] = 'file';
			}
			$header['offset'] = $this->file->tell();
			
			return $header;
		}
		else {
			return false;
		}
	}
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
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
 */
class File {
	protected $resource = null;
	protected $filename;
	
	/**
	 * Opens a new file.
	 *
	 * @param	string		$filename
	 * @param	string		$mode
	 * @throws	SystemException
	 */
	public function __construct($filename, $mode = 'wb') {
		$this->filename = $filename;
		$this->resource = fopen($filename, $mode);
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

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * The File class handles all file operations on a zipped file.
 *
 * @author	Marcel Werk
 */
class ZipFile extends File {
	/**
	 * checks if gz*64 functions are available instead of gz*
	 * https://bugs.php.net/bug.php?id=53829
	 * @var	boolean
	 */
	protected static $gzopen64 = null;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Opens a new zipped file.
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
		if (!self::$gzopen64 && !function_exists('gzopen')) {
			throw new SystemException('Can not find functions of the zlib extension');
		}
		$this->resource = (self::$gzopen64 ? @gzopen64($filename, $mode) : @gzopen($filename, $mode));
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
	 * Returns the filesize of the unzipped file
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

// let's go
// get temp file prefix
if (isset($_REQUEST['tmpFilePrefix'])) {
	$prefix = preg_replace('/[^a-f0-9_]+/', '', $_REQUEST['tmpFilePrefix']);
}
else {
	$prefix = substr(sha1(uniqid(microtime())), 0, 8);
}
define('TMP_FILE_PREFIX', $prefix);

// try to find the temp folder
define('TMP_DIR', BasicFileUtil::getInstallTempFolder());

/**
 * Reads a file resource from temp folder.
 * 
 * @param	string		$key
 * @param	string		$directory
 */
function readFileResource($key, $directory) {
	if (preg_match('~[\w\-]+\.(css|jpg|png|svg|eot|woff|ttf)~', $_GET[$key], $match)) {
		switch ($match[1]) {
			case 'css':
				header('Content-Type: text/css');
			break;
			
			case 'jpg':
				header('Content-Type: image/jpg');
			break;
			
			case 'png':
				header('Content-Type: image/png');
			break;
			
			case 'svg':
				header('Content-Type: image/svg+xml');
			break;
			
			case 'eot':
				header('Content-Type: application/vnd.ms-fontobject');
			break;
				
			case 'woff':
				header('Content-Type: application/font-woff');
			break;
					
			case 'ttf':
				header('Content-Type: application/octet-stream');
			break;
		}
		
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600).' GMT');
		header('Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: public, max-age=3600');
		
		readfile($directory . $_GET[$key]);
	}
	exit;
}

// show image from temp folder
if (isset($_GET['showImage'])) {
	readFileResource('showImage', TMP_DIR . 'install/files/acp/images/');
}
// show icon from temp folder
if (isset($_GET['showIcon'])) {
	readFileResource('showIcon', TMP_DIR . 'install/files/icon/');
}
// show css from temp folder
if (isset($_GET['showCSS'])) {
	readFileResource('showCSS', TMP_DIR . 'install/files/acp/style/setup/');
}
// show fonts from temp folder
if (isset($_GET['showFont'])) {
	readFileResource('showFont', TMP_DIR . 'install/files/font/');
}

// check whether setup files are already unzipped
if (!file_exists(TMP_DIR . 'install/files/lib/system/WCFSetup.class.php')) {
	// try to unzip all setup files into temp folder
	$tar = new Tar(SETUP_FILE);
	$contentList = $tar->getContentList();
	if (empty($contentList)) {
		throw new SystemException("Cannot unpack 'WCFSetup.tar.gz'. File is probably broken.");
	}
	
	foreach ($contentList as $file) {
		foreach ($neededFilesPattern as $pattern) {
			if (preg_match($pattern, $file['filename'])) {
				// create directory if not exists
				$dir = TMP_DIR . dirname($file['filename']);
				if (!@is_dir($dir)) {
					@mkdir($dir, 0777, true);
					BasicFileUtil::makeWritable($dir);
				}
				
				$tar->extract($file['index'], TMP_DIR . $file['filename']);
			}
		}
	}
	$tar->close();
	
	// create cache folders
	@mkdir(TMP_DIR . 'setup/lang/cache/', 0777);
	BasicFileUtil::makeWritable(TMP_DIR . 'setup/lang/cache/');
	
	@mkdir(TMP_DIR . 'setup/template/compiled/', 0777);
	BasicFileUtil::makeWritable(TMP_DIR . 'setup/template/compiled/');
}

if (!class_exists('wcf\system\WCFSetup')) {
	throw new SystemException("Cannot find class 'WCFSetup'");
}

// start setup
new \wcf\system\WCFSetup();

<?php // @codingStandardsIgnoreFile
/**
 * This script tries to find the temp folder and unzip all setup files into.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

define('INSTALL_SCRIPT', __FILE__);
define('INSTALL_SCRIPT_DIR', dirname(__FILE__) . '/');

define('WCF_DIR', INSTALL_SCRIPT_DIR);
define('RELATIVE_WCF_DIR', './');

define('SETUP_FILE', INSTALL_SCRIPT_DIR . 'WCFSetup.tar.gz');

define('NO_IMPORTS', 1);

$neededFilesPattern = [
	'!^setup/.*!',
	'!^install/files/acp/images/woltlabSuite.*!',
	'!^install/files/acp/style/setup/.*!',
	'!^install/files/lib/data/.*!',
	'!^install/files/lib/system/.*!',
	'!^install/files/lib/util/.*!',
	'!^install/lang/.*!',
	'!^install/packages/.*!',
];

function sanitizeStacktrace(\Throwable $e, bool $ignorePaths = false)
{
	$trace = $e->getTrace();

	return array_map(function ($item) use ($ignorePaths) {
		if (!isset($item['file'])) $item['file'] = '[internal function]';
		if (!isset($item['line'])) $item['line'] = '?';
		if (!isset($item['class'])) $item['class'] = '';
		if (!isset($item['type'])) $item['type'] = '';
		if (!isset($item['args'])) $item['args'] = [];

		try {
			if (!empty($item['args'])) {
				if ($item['class']) {
					$function = new \ReflectionMethod($item['class'], $item['function']);
				} else {
					$function = new \ReflectionFunction($item['function']);
				}

				$parameters = $function->getParameters();
				$i = 0;
				foreach ($parameters as $parameter) {
					$isSensitive = false;
					if (
						!empty($parameter->getAttributes(\wcf\SensitiveArgument::class))
						|| !empty($parameter->getAttributes(\SensitiveParameter::class))
					) {
						$isSensitive = true;
					}
					if (\preg_match(
						'/(?:^(?:password|passphrase|secret)|(?:Password|Passphrase|Secret))/',
						$parameter->getName()
					)) {
						$isSensitive = true;
					}

					if ($isSensitive && isset($item['args'][$i])) {
						$item['args'][$i] = '[redacted]';
					}
					$i++;
				}

				// strip database credentials
				if (
					preg_match('~\\\\?wcf\\\\system\\\\database\\\\[a-zA-Z]*Database~', $item['class'])
					|| $item['class'] === 'PDO'
				) {
					if ($item['function'] === '__construct') {
						$item['args'] = array_map(function () {
							return '[redacted]';
						}, $item['args']);
					}
				}
			}
		} catch (\Throwable $e) {
			$item['args'] = array_map(function () {
				return '[error_during_sanitization]';
			}, $item['args']);
		}

		if (!$ignorePaths) {
			$item['args'] = array_map(function ($item) {
				if (!is_string($item)) return $item;

				if (preg_match('~^(' . preg_quote($_SERVER['DOCUMENT_ROOT'], '~') . '|' . preg_quote(WCF_DIR, '~') . ')~', $item)) {
					$item = sanitizePath($item);
				}

				return $item;
			}, $item['args']);

			$item['file'] = sanitizePath($item['file']);
		}

		return $item;
	}, $trace);
}

function printException($e)
{
	$exceptionTitle = 'An error has occurred';
	$exceptionSubtitle = '';
	$exceptionExplanation = '';
	$exceptionID = '';
?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta charset="utf-8">
		<?php if (!defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
			<title>Fatal Error: <?php echo htmlspecialchars($e->getMessage()); ?></title>
		<?php } else { ?>
			<title>Fatal Error</title>
		<?php } ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			.exceptionBody {
				background-color: rgb(250, 250, 250);
				color: rgb(44, 62, 80);
				margin: 0;
				padding: 0;
			}

			.exceptionContainer {
				box-sizing: border-box;
				font-family: 'Segoe UI', 'Lucida Grande', 'Helvetica Neue', Helvetica, Arial, sans-serif;
				font-size: 14px;
				padding-bottom: 20px;
			}

			.exceptionContainer * {
				box-sizing: inherit;
				line-height: 1.5em;
				margin: 0;
				padding: 0;
			}

			.exceptionHeader {
				background-color: rgb(58, 109, 156);
				padding: 30px 0;
			}

			.exceptionTitle {
				color: #fff;
				font-size: 28px;
				font-weight: 300;
			}

			.exceptionErrorCode {
				color: #fff;
				margin-top: .5em;
			}

			.exceptionErrorCode .exceptionInlineCode {
				background-color: rgb(43, 79, 113);
				border-radius: 3px;
				color: #fff;
				font-family: monospace;
				padding: 3px 10px;
				white-space: nowrap;
			}

			.exceptionSubtitle {
				border-bottom: 1px solid rgb(238, 238, 238);
				font-size: 24px;
				font-weight: 300;
				margin-bottom: 15px;
				padding-bottom: 10px;
			}

			.exceptionContainer>.exceptionBoundary {
				margin-top: 30px;
			}

			.exceptionText .exceptionInlineCodeWrapper {
				border: 1px solid rgb(169, 169, 169);
				border-radius: 3px;
				padding: 2px 5px;
			}

			.exceptionText .exceptionInlineCode {
				font-family: monospace;
				white-space: nowrap;
			}

			.exceptionFieldTitle {
				color: rgb(59, 109, 169);
			}

			.exceptionFieldTitle .exceptionColon {
				/* hide colon in browser, but will be visible after copy & paste */
				opacity: 0;
			}

			.exceptionFieldValue {
				font-size: 18px;
				min-height: 1.5em;
			}

			pre.exceptionFieldValue {
				font-size: 14px;
				white-space: pre-wrap;
			}

			.exceptionSystemInformation,
			.exceptionErrorDetails,
			.exceptionStacktrace {
				list-style-type: none;
			}

			.exceptionSystemInformation>li:not(:first-child),
			.exceptionErrorDetails>li:not(:first-child) {
				margin-top: 10px;
			}

			.exceptionStacktrace {
				display: block;
				margin-top: 5px;
				overflow: auto;
				padding-bottom: 20px;
			}

			.exceptionStacktraceFile,
			.exceptionStacktraceFile span,
			.exceptionStacktraceCall,
			.exceptionStacktraceCall span {
				font-family: monospace !important;
				white-space: nowrap !important;
			}

			.exceptionStacktraceCall+.exceptionStacktraceFile {
				margin-top: 5px;
			}

			.exceptionStacktraceCall {
				padding-left: 40px;
			}

			.exceptionStacktraceCall,
			.exceptionStacktraceCall span {
				color: rgb(102, 102, 102) !important;
				font-size: 13px !important;
			}

			/* mobile */
			@media (max-width: 767px) {
				.exceptionBoundary {
					min-width: 320px;
					padding: 0 10px;
				}

				.exceptionText .exceptionInlineCodeWrapper {
					display: inline-block;
					overflow: auto;
				}

				.exceptionErrorCode .exceptionInlineCode {
					font-size: 13px;
					padding: 2px 5px;
				}
			}

			/* desktop */
			@media (min-width: 768px) {
				.exceptionBoundary {
					margin: 0 auto;
					max-width: 1400px;
					min-width: 1200px;
					padding: 0 10px;
				}

				.exceptionSystemInformation {
					display: flex;
					flex-wrap: wrap;
				}

				.exceptionSystemInformation1,
				.exceptionSystemInformation3,
				.exceptionSystemInformation5 {
					flex: 0 0 200px;
					margin: 0 0 10px 0 !important;
				}

				.exceptionSystemInformation2,
				.exceptionSystemInformation4,
				.exceptionSystemInformation6 {
					flex: 0 0 calc(100% - 210px);
					margin: 0 0 10px 10px !important;
					max-width: calc(100% - 210px);
				}

				.exceptionSystemInformation1 {
					order: 1;
				}

				.exceptionSystemInformation2 {
					order: 2;
				}

				.exceptionSystemInformation3 {
					order: 3;
				}

				.exceptionSystemInformation4 {
					order: 4;
				}

				.exceptionSystemInformation5 {
					order: 5;
				}

				.exceptionSystemInformation6 {
					order: 6;
				}

				.exceptionSystemInformation .exceptionFieldValue {
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}
			}
		</style>
	</head>

	<body class="exceptionBody">
		<div class="exceptionContainer">
			<div class="exceptionHeader">
				<div class="exceptionBoundary">
					<p class="exceptionTitle"><?php echo $exceptionTitle; ?></p>
					<p class="exceptionErrorCode"><?php echo str_replace('{$exceptionID}', $exceptionID, $exceptionSubtitle); ?></p>
				</div>
			</div>

			<div class="exceptionBoundary">
				<?php echo $exceptionExplanation; ?>
			</div>
			<?php if (!defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
				<div class="exceptionBoundary">
					<p class="exceptionSubtitle">System Information</p>
					<ul class="exceptionSystemInformation">
						<li class="exceptionSystemInformation1">
							<p class="exceptionFieldTitle">PHP Version<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue"><?php echo htmlspecialchars(phpversion()); ?></p>
						</li>
						<li class="exceptionSystemInformation3">
							<p class="exceptionFieldTitle">WoltLab Suite Core<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue">n/a</p>
						</li>
						<li class="exceptionSystemInformation5">
							<p class="exceptionFieldTitle">Peak Memory Usage<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue"><?php echo round(memory_get_peak_usage() / 1024 / 1024, 3); ?> MiB</p>
						</li>
						<li class="exceptionSystemInformation2">
							<p class="exceptionFieldTitle">Request URI<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue"><?php if (isset($_SERVER['REQUEST_METHOD'])) echo htmlspecialchars($_SERVER['REQUEST_METHOD']); ?> <?php if (isset($_SERVER['REQUEST_URI'])) echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></p>
						</li>
						<li class="exceptionSystemInformation4">
							<p class="exceptionFieldTitle">Referrer<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_REFERER'])) echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?></p>
						</li>
						<li class="exceptionSystemInformation6">
							<p class="exceptionFieldTitle">User Agent<span class="exceptionColon">:</span></p>
							<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_USER_AGENT'])) echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></p>
						</li>
					</ul>
				</div>

				<?php
				$first = true;
				$exceptions = [];
				$current = $e;
				do {
					$exceptions[] = $current;
				} while ($current = $current->getPrevious());

				$e = array_pop($exceptions);
				do {
				?>
					<div class="exceptionBoundary">
						<p class="exceptionSubtitle"><?php if (!empty($exceptions) && $first) {
															echo "Original ";
														} else if (empty($exceptions) && !$first) {
															echo "Final ";
														} ?>Error</p>
						<?php if ($e instanceof SystemException && $e->getDescription()) { ?>
							<p class="exceptionText"><?php echo $e->getDescription(); ?></p>
						<?php } ?>
						<ul class="exceptionErrorDetails">
							<li>
								<p class="exceptionFieldTitle">Error Type<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo htmlspecialchars(get_class($e)); ?></p>
							</li>
							<li>
								<p class="exceptionFieldTitle">Error Message<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo htmlspecialchars($e->getMessage()); ?></p>
							</li>
							<?php if ($e->getCode()) { ?>
								<li>
									<p class="exceptionFieldTitle">Error Code<span class="exceptionColon">:</span></p>
									<p class="exceptionFieldValue"><?php echo htmlspecialchars($e->getCode()); ?></p>
								</li>
							<?php } ?>
							<li>
								<p class="exceptionFieldTitle">File<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue" style="word-break: break-all"><?php echo htmlspecialchars(($e->getFile())); ?> (<?php echo $e->getLine(); ?>)</p>
							</li>

							<?php
							if ($e instanceof SystemException) {
								ob_start();
								$e->show();
								ob_end_clean();

								$reflection = new \ReflectionClass($e);
								$property = $reflection->getProperty('information');
								if ($property->getValue($e)) {
									throw new \Exception("Using the 'information' property of SystemException is not supported any more.");
								}
							}
							if ($e instanceof IExtraInformationException) {
								foreach ($e->getExtraInformation() as list($key, $value)) {
							?>
									<li>
										<p class="exceptionFieldTitle"><?php echo htmlspecialchars($key); ?><span class="exceptionColon">:</span></p>
										<p class="exceptionFieldValue"><?php echo htmlspecialchars($value); ?></p>
									</li>
								<?php
								}
							}

							$templateContextLines = [];
							if (!empty($templateContextLines)) {
								?>
								<li>
									<p class="exceptionFieldTitle">Template Context<span class="exceptionColon">:</span></p>
									<pre class="exceptionFieldValue"><?php echo htmlspecialchars(implode("", $templateContextLines)); ?></pre>
								</li>
							<?php
							}
							?>
							<li>
								<p class="exceptionFieldTitle">Stack Trace<span class="exceptionColon">:</span></p>
								<ul class="exceptionStacktrace">
									<?php
									$trace = sanitizeStacktrace($e, true);
									for ($i = 0, $max = count($trace); $i < $max; $i++) {
									?>
										<li class="exceptionStacktraceFile"><?php echo '#' . $i . ' ' . htmlspecialchars($trace[$i]['file']) . ' (' . $trace[$i]['line'] . ')' . ':'; ?></li>
										<li class="exceptionStacktraceCall">
										<?php
										echo $trace[$i]['class'] . $trace[$i]['type'] . $trace[$i]['function'] . '(';
										echo implode(', ', array_map(function ($item) {
											switch (gettype($item)) {
												case 'integer':
												case 'double':
													return $item;
												case 'NULL':
													return 'null';
												case 'string':
													return "'" . addcslashes(htmlspecialchars($item), "\\'") . "'";
												case 'boolean':
													return $item ? 'true' : 'false';
												case 'array':
													$keys = array_keys($item);
													if (count($keys) > 5) return "[ " . count($keys) . " items ]";
													return '[ ' . implode(', ', array_map(function ($item) {
														return $item . ' => ';
													}, $keys)) . ']';
												case 'object':
													return get_class($item);
												case 'resource':
													return 'resource(' . get_resource_type($item) . ')';
												case 'resource (closed)':
													return 'resource (closed)';
											}

											throw new \LogicException('Unreachable');
										}, $trace[$i]['args']));
										echo ')</li>';
									}
										?>
								</ul>
							</li>
						</ul>
					</div>
				<?php
					$first = false;
				} while ($e = array_pop($exceptions));
				?>
			<?php } ?>
		</div>
	</body>

	</html>
<?php
}

set_exception_handler(static function ($e) {
	try {
		if ($e instanceof \wcf\system\exception\IPrintableException) {
			$e->show();
			exit;
		}

		// repacking
		printException($e);
		exit;
	} catch (\Throwable $exception) {
		die("<pre>WCF::handleException() Unhandled exception: " . $exception->getMessage() . "\n\n" . $exception->getTraceAsString());
	}
});
set_error_handler(static function ($severity, $message, $file, $line) {
	// this is necessary for the shut-up operator
	if (!(\error_reporting() & $severity)) {
		return;
	}

	throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * A SystemException is thrown when an unexpected error occurs.
 *
 * @package	com.woltlab.wcf
 * @author	Marcel Werk
 */
class SystemException extends \Exception
{
	protected $description;
	protected $information = '';
	protected $functions = '';

	/**
	 * Creates a new SystemException.
	 *
	 * @param	string		$message	error message
	 * @param	int		$code		error code
	 * @param	string		$description	description of the error
	 * @param	\Exception	$previous	repacked Exception
	 */
	public function __construct($message = '', $code = 0, $description = '', \Exception $previous = null)
	{
		parent::__construct((string) $message, (int) $code, $previous);
		$this->description = $description;
	}

	/**
	 * Returns the description of this exception.
	 *
	 * @return	string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Prints this exception.
	 * This method is called by WCF::handleException().
	 */
	public function show()
	{
	}
}

/**
 * Loads the required classes automatically.
 */
spl_autoload_register(function ($className) {
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
 * Helper method to output debug data for all passed variables,
 * uses `print_r()` for arrays and objects, `var_dump()` otherwise.
 */
function wcfDebug()
{
	echo "<pre>";

	$args = func_get_args();
	$length = count($args);
	if ($length === 0) {
		echo "ERROR: No arguments provided.<hr>";
	} else {
		for ($i = 0; $i < $length; $i++) {
			$arg = $args[$i];

			echo "<h2>Argument {$i} (" . gettype($arg) . ")</h2>";

			if (is_array($arg) || is_object($arg)) {
				print_r($arg);
			} else {
				var_dump($arg);
			}

			echo "<hr>";
		}
	}

	$backtrace = debug_backtrace();

	// output call location to help finding these debug outputs again
	echo "wcfDebug() called in {$backtrace[0]['file']} on line {$backtrace[0]['line']}";

	echo "</pre>";

	exit;
}

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/**
 * BasicFileUtil contains file-related functions.
 *
 * @package	com.woltlab.wcf
 * @author	Marcel Werk
 */
class BasicFileUtil
{
	/**
	 * chmod mode
	 * @var	int
	 */
	protected static $mode = null;

	/**
	 * Tries to make a file or directory writable. It starts of with the least
	 * permissions and goes up until 0666 for files and 0777 for directories.
	 *
	 * @param	string		$filename
	 * @throws	\Exception
	 */
	public static function makeWritable($filename)
	{
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
			} else {
				// anything but Windows
				clearstatcache();

				self::$mode = 0666;

				$tmpFilename = '__permissions_' . sha1(time()) . '.txt';
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
			} else {
				@chmod($filename, 0777);
			}
		} else {
			@chmod($filename, self::$mode);
		}

		if (!is_writable($filename)) {
			throw new \Exception("Unable to make '" . $filename . "' writable. This is a misconfiguration of your server, please contact your system administrator or hosting provider.");
		}
	}

	/**
	 * Adds a trailing slash to the given path.
	 *
	 * @param	string		$path
	 */
	public static function addTrailingSlash($path): string
	{
		return rtrim($path, '/') . '/';
	}

	/**
	 * Creates a path on the local filesystem and returns true on success.
	 * Parent directories do not need to exists as they will be created if
	 * necessary.
	 *
	 * @param	string		$path
	 */
	public static function makePath($path): bool
	{
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
class Tar
{
	/**
	 * name of the archive
	 * @var	string
	 */
	protected $archiveName = '';

	/**
	 * content of the tar file
	 * @var	array
	 */
	protected $contentList = [];

	/**
	 * indicates if tar file is opened
	 * @var	bool
	 */
	protected $opened = false;

	/**
	 * indicates if file content has been read
	 * @var	bool
	 */
	protected $read = false;

	/**
	 * file object
	 * @var	File
	 */
	protected $file = null;

	/**
	 * indicates if the tar file is (g)zipped
	 * @var	bool
	 */
	protected $isZipped = false;

	/**
	 * file access mode
	 * @var	string
	 */
	protected $mode = 'rb';

	/**
	 * chunk size for extracting
	 * @var	int
	 */
	const CHUNK_SIZE = 8192;

	private static array $asciiMap;

	/**
	 * Creates a new Tar object.
	 * archiveName must be tarball or gzipped tarball
	 *
	 * @param	string		$archiveName
	 * @throws	SystemException
	 */
	public function __construct($archiveName)
	{
		if (!is_file($archiveName)) {
			throw new SystemException("unable to find tar archive '" . $archiveName . "'");
		}

		if (!isset(self::$asciiMap)) {
			self::$asciiMap = [];
			for ($i = 0; $i <= 0xFF; $i++) {
				self::$asciiMap[\chr($i)] = $i;
			}
		}

		$this->archiveName = $archiveName;
		$this->open();
		$this->readContent();
	}

	/**
	 * Destructor of this class, closes tar archive.
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Opens the tar archive and stores filehandle.
	 */
	public function open()
	{
		if (!$this->opened) {
			if ($this->isZipped) $this->file = new GZipFile($this->archiveName, $this->mode);
			else {
				// test compression
				$this->file = new File($this->archiveName, $this->mode);
				if ($this->file->read(2) == "\37\213") {
					$this->file->close();
					$this->isZipped = true;
					$this->file = new GZipFile($this->archiveName, $this->mode);
				} else {
					$this->file->seek(0);
				}
			}
			$this->opened = true;
		}
	}

	/**
	 * Closes the opened file.
	 */
	public function close()
	{
		if ($this->opened) {
			$this->file->close();
			$this->opened = false;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getContentList()
	{
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		return $this->contentList;
	}

	/**
	 * @inheritDoc
	 */
	public function getFileInfo($fileIndex)
	{
		if (!is_int($fileIndex)) {
			$fileIndex = $this->getIndexByFilename($fileIndex);
		}

		if (!isset($this->contentList[$fileIndex])) {
			throw new SystemException("Tar: could find file '" . $fileIndex . "' in archive");
		}
		return $this->contentList[$fileIndex];
	}

	/**
	 * @inheritDoc
	 */
	public function getIndexByFilename($filename)
	{
		foreach ($this->contentList as $index => $file) {
			if ($file['filename'] == $filename) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function extractToString($index)
	{
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
		$content = $this->file->read($header['size']);

		if (strlen($content) != $header['size']) {
			throw new SystemException("Could not untar file '" . $header['filename'] . "' to string. Maybe the archive is truncated?");
		}

		return $content;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($index, $destination)
	{
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);

		BasicFileUtil::makePath(dirname($destination));
		if ($header['type'] === 'folder') {
			BasicFileUtil::makePath($destination);
			return;
		}
		if ($header['type'] === 'symlink') {
			// skip symlinks
			return;
		}

		// seek to offset
		$this->file->seek($header['offset']);

		$targetFile = new File($destination);

		// read and write data
		if ($header['size']) {
			$buffer = $this->file->read($header['size']);
			$targetFile->write($buffer);
		}
		$targetFile->close();

		BasicFileUtil::makeWritable($destination);

		if ($header['mtime']) {
			@$targetFile->touch($header['mtime']);
		}

		// check filesize
		if (filesize($destination) != $header['size']) {
			throw new SystemException("Could not untar file '" . $header['filename'] . "' to '" . $destination . "'. Maybe disk quota exceeded in folder '" . dirname($destination) . "'.");
		}

		return true;
	}

	/**
	 * Reads table of contents (TOC) from tar archive.
	 * This does not get the entire to memory but only parts of it.
	 */
	protected function readContent()
	{
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
				$format = 'Z' . $header['size'] . 'filename';

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

			$this->file->seek($this->file->tell() + (512 * ceil($header['size'] / 512)));
		}
	}

	/**
	 * Unpacks file header for one file entry.
	 *
	 * @param	string		$binaryData
	 * @return	array|bool
	 */
	protected function readHeader($binaryData)
	{
		if (strlen($binaryData) != 512) {
			return false;
		}

		$header = [];
		$checksum = 0;
		// First part of the header
		for ($i = 0; $i < 148; $i++) {
			$checksum += self::$asciiMap[$binaryData[$i]];
		}
		// Calculate the checksum
		// Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++) {
			$checksum += self::$asciiMap[' '];
		}
		// Last part of the header
		for ($i = 156; $i < 512; $i++) {
			$checksum += self::$asciiMap[$binaryData[$i]];
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
				$header['filename'] = $header['prefix'] . '/' . $header['filename'];
			}
			$header['typeflag'] = $data['typeflag'];
			if ($header['typeflag'] == '5') {
				$header['size'] = 0;
				$header['type'] = 'folder';
			} else if ($header['typeflag'] == '2') {
				$header['type'] = 'symlink';
				$header['target'] = $data['link'];
			} else {
				$header['type'] = 'file';
			}
			$header['offset'] = $this->file->tell();

			return $header;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if this tar is (g)zipped.
	 *
	 * @return	bool
	 */
	public function isZipped()
	{
		return $this->isZipped;
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
class File
{
	protected $resource = null;
	protected $filename;

	/**
	 * Opens a new file.
	 *
	 * @param	string		$filename
	 * @param	string		$mode
	 * @throws	SystemException
	 */
	public function __construct($filename, $mode = 'wb')
	{
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
	public function __call($function, $arguments)
	{
		if (function_exists('f' . $function)) {
			array_unshift($arguments, $this->resource);
			return call_user_func_array('f' . $function, $arguments);
		} else if (function_exists($function)) {
			array_unshift($arguments, $this->filename);
			return call_user_func_array($function, $arguments);
		} else {
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
final class GZipFile extends File
{
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Opens a gzip file.
	 *
	 * @param	string		$filename
	 * @param	string		$mode
	 * @throws	SystemException
	 */
	public function __construct($filename, $mode = 'wb')
	{
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
	 * @return	mixed
	 * @throws	SystemException
	 */
	public function __call($function, $arguments)
	{
		if (function_exists('gz' . $function)) {
			array_unshift($arguments, $this->resource);
			return call_user_func_array('gz' . $function, $arguments);
		} else if (function_exists($function)) {
			array_unshift($arguments, $this->filename);
			return call_user_func_array($function, $arguments);
		} else {
			throw new SystemException('Can not call method ' . $function);
		}
	}

	/**
	 * @see \gzread()
	 */
	public function read(int $length): string|false
	{
		return \gzread($this->resource, $length);
	}

	/**
	 * @see \gztell()
	 */
	public function tell(): int|false
	{
		return \gztell($this->resource);
	}

	/**
	 * @see \gzseek()
	 */
	public function seek(int $offset, int $whence = \SEEK_SET): int
	{
		return \gzseek($this->resource, $offset, $whence);
	}

	/**
	 * Returns the filesize of the unzipped file.
	 *
	 * @return	int
	 */
	public function getFileSize()
	{
		$byteBlock = 1 << 14;
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

// Bootstrap Setup.

$prefix = null;
if (isset($_POST['tmpFilePrefix'])) {
	$inputPrefix = \preg_replace('/[^a-f0-9_]+/', '', $_POST['tmpFilePrefix']);

	if (\is_dir(INSTALL_SCRIPT_DIR . "/WCFSetup-{$inputPrefix}/")) {
		// We accept the input prefix if a corresponding directory exists.
		$prefix = $inputPrefix;
	}
}

// If no trusted prefix was provided, we generate a random prefix and corresponding directory.
if ($prefix === null) {
	$prefix = \bin2hex(\random_bytes(8));
	$dir = INSTALL_SCRIPT_DIR . "/WCFSetup-{$prefix}/";
	\mkdir($dir);
	BasicFileUtil::makeWritable($dir);
	\file_put_contents($dir . 'lastStep', '0');
}

\define('TMP_FILE_PREFIX', $prefix);
\define('TMP_DIR', INSTALL_SCRIPT_DIR . "/WCFSetup-{$prefix}/");

// check whether setup files are already unzipped
if (!file_exists(TMP_DIR . 'install/files/lib/system/WCFSetup.class.php')) {
	// try to unzip all setup files into temp folder
	$tar = new Tar(SETUP_FILE);
	$contentList = $tar->getContentList();
	if (empty($contentList)) {
		throw new \Exception("Cannot unpack 'WCFSetup.tar.gz'. File is probably broken.");
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

	@mkdir(TMP_DIR . 'setup/template/compiled/', 0777);
	BasicFileUtil::makeWritable(TMP_DIR . 'setup/template/compiled/');
}

if (!class_exists(\wcf\system\WCFSetup::class)) {
	throw new \Exception(\sprintf(
		"Cannot find class '%s'",
		\wcf\system\WCFSetup::class
	));
}

// start setup
new \wcf\system\WCFSetup();

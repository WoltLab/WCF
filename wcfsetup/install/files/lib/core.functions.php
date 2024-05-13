<?php // @codingStandardsIgnoreFile
/**
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

namespace {

	use wcf\system\WCF;

	// set exception handler
	set_exception_handler([WCF::class, 'handleException']);
	// set php error handler
	set_error_handler([WCF::class, 'handleError'], E_ALL);

	// set shutdown function
	register_shutdown_function([WCF::class, 'destruct']);
	// set autoload function
	spl_autoload_register([WCF::class, 'autoload'], true, true);

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

	// define DOCUMENT_ROOT on IIS if not set
	if (PHP_EOL == "\r\n") {
		if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}

		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
			if (isset($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
}

namespace wcf {
	function getRequestId(): string
	{
		if (!defined('WCF_REQUEST_ID_HEADER') || !WCF_REQUEST_ID_HEADER) return '';

		return $_SERVER[WCF_REQUEST_ID_HEADER] ?? '';
	}

	function getMinorVersion(): string
	{
		return preg_replace('/^(\d+\.\d+)\..*$/', '\\1', WCF_VERSION);
	}

	/**
	 * @deprecated 6.0 Use #[\SensitiveParameter] instead.
	 */
	#[\Attribute(\Attribute::TARGET_PARAMETER)]
	class SensitiveArgument
	{
	}
}

namespace wcf\functions\exception {

	use wcf\http\Pipeline;
	use wcf\system\WCF;
	use wcf\system\exception\IExtraInformationException;
	use wcf\system\exception\SystemException;
	use wcf\system\request\Request;
	use wcf\util\FileUtil;
	use wcf\util\StringUtil;

	/**
	 * If the stacktrace contains a compiled template, the context of the relevant template line
	 * is returned, otherwise an empty array is returned.
	 */
	function getTemplateContextLines(\Throwable $e): array
	{
		try {
			$contextLineCount = 5;
			foreach ($e->getTrace() as $traceEntry) {
				if (isset($traceEntry['file']) && \preg_match(
					'~/templates/compiled/.+\.php$~',
					$traceEntry['file']
				)) {
					$startLine = $traceEntry['line'] - $contextLineCount;
					$relativeErrorLine = $contextLineCount;
					if ($startLine < 0) {
						$startLine = 0;
						$relativeErrorLine = $traceEntry['line'] - 1;
					}

					$file = \fopen($traceEntry['file'], 'r');
					if (!$file) {
						return [];
					}

					for ($line = 0; $line < $startLine; $line++) {
						if (\substr(\fgets($file, 1024), -1) !== "\n") {
							// We don't want to handle a file where lines exceed 1024 Bytes.
							return [];
						}
					}

					$maxLineCount = 2 * $contextLineCount + 1;
					$lines = [];
					while (!\feof($file) && \count($lines) < $maxLineCount) {
						$line = \fgets($file, 1024);
						if (\substr($line, -1) !== "\n" && !\feof($file)) {
							// We don't want to handle a file where lines exceed 1024 Bytes.
							return [];
						}

						if (count($lines) === $relativeErrorLine - 1) {
							$line = "====> {$line}";
						}

						$lines[] = $line;
					}

					return $lines;
				}
			}
		} catch (\Throwable $e) {
			// Ignore errors while extracting the template context to be saved in the exception log.
		}

		return [];
	}

	/**
	 * Logs the given Throwable.
	 *
	 * @param	string			$logFile	The log file to use. If set to `null` the default log file will be used and the variable contents will be replaced by the actual path.
	 * @return	string					The ID of the log entry.
	 */
	function logThrowable(\Throwable $e, &$logFile = null): string
	{
		if ($logFile === null) $logFile = WCF_DIR . 'log/' . gmdate('Y-m-d', TIME_NOW) . '.txt';
		touch($logFile);

		$stripNewlines = function ($item) {
			return str_replace("\n", ' ', $item);
		};

		$getExtraInformation = function (\Throwable $e) {
			$extraInformation = [];

			if ($e instanceof IExtraInformationException) {
				$extraInformation = $e->getExtraInformation();
			}

			$templateContextLines = getTemplateContextLines($e);
			if (!empty($templateContextLines)) {
				$extraInformation[] = [
					'Template Context',
					\implode("", $templateContextLines),
				];
			}

			return !empty($extraInformation) ? base64_encode(serialize($extraInformation)) : "-";
		};

		// don't forget to update ExceptionLogUtil / ExceptionLogViewPage, when changing the log file format
		$message = gmdate('r', TIME_NOW) . "\n" .
			'Message: ' . $stripNewlines($e->getMessage()) . "\n" .
			'PHP version: ' . phpversion() . "\n" .
			'WoltLab Suite version: ' . WCF_VERSION . "\n" .
			'Request URI: ' . $stripNewlines(($_SERVER['REQUEST_METHOD'] ?? '') . ' ' . ($_SERVER['REQUEST_URI'] ?? '')) . (\wcf\getRequestId() ? ' (' . \wcf\getRequestId() . ')' : '') . "\n" .
			'Referrer: ' . $stripNewlines($_SERVER['HTTP_REFERER'] ?? '') . "\n" .
			'User Agent: ' . $stripNewlines($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n" .
			'Peak Memory Usage: ' . memory_get_peak_usage() . '/' . FileUtil::getMemoryLimit() . "\n";
		$prev = $e;
		do {
			$message .= "======\n" .
				'Error Class: ' . get_class($prev) . "\n" .
				'Error Message: ' . $stripNewlines($prev->getMessage()) . "\n" .
				'Error Code: ' . $stripNewlines($prev->getCode()) . "\n" .
				'File: ' . $stripNewlines($prev->getFile()) . ' (' . $prev->getLine() . ')' . "\n" .
				'Extra Information: ' . $getExtraInformation($prev) . "\n" .
				'Stack Trace: ' . json_encode(array_map(function ($item) {
					$item['args'] = array_map(function ($item) {
						switch (gettype($item)) {
							case 'object':
								if ($item instanceof \UnitEnum) {
									return $item::class . '::' . $item->name;
								}

								return $item::class;
							case 'array':
								return array_map(function () {
									return '[redacted]';
								}, $item);
							case 'resource':
								return 'resource(' . get_resource_type($item) . ')';
							default:
								return $item;
						}
					}, $item['args']);

					return $item;
				}, sanitizeStacktrace($prev, true))) . "\n";
		} while ($prev = $prev->getPrevious());

		// calculate Exception-ID
		$exceptionID = sha1($message);
		$entry = "<<<<<<<<" . $exceptionID . "<<<<\n" . $message . "<<<<\n\n";

		file_put_contents($logFile, $entry, FILE_APPEND);

		return $exceptionID;
	}

	/**
	 * Pretty prints the given Throwable. It is recommended to `exit;`
	 * the request after calling this function.
	 *
	 * @throws	\Exception
	 */
	function printThrowable(\Throwable $e)
	{
		$exceptionID = logThrowable($e, $logFile);
		if (\wcf\getRequestId()) $exceptionID .= '/' . \wcf\getRequestId();

		$exceptionTitle = $exceptionSubtitle = $exceptionExplanation = '';
		$logFile = sanitizePath($logFile);
		try {
			if (WCF::getLanguage() !== null) {
				$exceptionTitle = WCF::getLanguage()->get('wcf.global.exception.title', true);
				$exceptionSubtitle = str_replace('{$exceptionID}', $exceptionID, WCF::getLanguage()->get('wcf.global.exception.subtitle', true));
				$exceptionExplanation = str_replace('{$logFile}', $logFile, WCF::getLanguage()->get('wcf.global.exception.explanation', true));
			}
		} catch (\Throwable $e) {
			// ignore
		}

		if (!$exceptionTitle || !$exceptionSubtitle || !$exceptionExplanation) {
			// one or more failed, fallback to english
			$exceptionTitle = 'An error has occurred';
			$exceptionSubtitle = 'Internal error code: <span class="exceptionInlineCodeWrapper"><span class="exceptionInlineCode">' . $exceptionID . '</span></span>';
			$exceptionExplanation = <<<EXPLANATION
<p class="exceptionSubtitle">What happened?</p>
<p class="exceptionText">An error has occured while trying to handle your request and execution has been terminated. Please forward the above error code to the site administrator.</p>
<p class="exceptionText">&nbsp;</p> <!-- required to ensure spacing after copy & paste -->
<p class="exceptionText">
	The error code can be used by an administrator to lookup the full error message in the Administration Control Panel via “Logs » Errors”.
	In addition the error has been written to the log file located at <span class="exceptionInlineCodeWrapper"><span class="exceptionInlineCode">{$logFile}</span></span> and can be accessed with an FTP program or similar.
</p>
<p class="exceptionText">&nbsp;</p> <!-- required to ensure spacing after copy & paste -->
<p class="exceptionText">Notice: The error code was randomly generated and has no use beyond looking up the full message.</p>
EXPLANATION;
		}

		/*
		 * A notice on the HTML used below:
		 *
		 * It might appear a bit weird to use <p> all over the place where semantically
		 * other elements would fit in way better. The reason behind this is that we avoid
		 * inheriting unwanted styles (e.g. exception displayed in an overlay) and that
		 * the output needs to be properly readable when copied & pasted somewhere.
		 *
		 * Besides the visual appearance, the output was built to provide a maximum of
		 * compatibility and readability when pasted somewhere else, e.g. a WYSIWYG editor
		 * without the potential of messing up the formatting and thus harming the readability.
		 */
?>
		<!DOCTYPE html>
		<html>

		<head>
			<meta charset="utf-8">
			<?php if (!defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
				<title>Fatal Error: <?php echo StringUtil::encodeHTML($e->getMessage()); ?></title>
			<?php } else { ?>
				<title>Fatal Error</title>
			<?php } ?>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<style>
				:root {
					--body-background-color: rgb(250, 250, 250);
					--body-color: rgb(44, 62, 80);
					--header-background-color: rgb(58, 109, 156);
					--title-color: #fff;
					--error-code-color: #fff;
					--inline-code-background-color: rgb(43, 79, 113);
					--inline-code-color: #fff;
					--inline-code-wrapper-border-color: rgb(169, 169, 169);
					--field-title-color: rgb(59, 109, 169);
					--exception-details-color: rgb(115 115 115);
					--middleware-border-color: #ccc;
					--sensitive-parameter-value-border-color: #d81b60;
					--stacktrace-counter-color: rgb(115 115 115);
				}

				@media (prefers-color-scheme: dark) {
					:root {
						--body-background-color: rgb(34 37 41);
						--body-color: rgb(209 210 211);
						--header-background-color: rgb(36 46 61);
						--title-color: rgb(209 210 211);
						--error-code-color: rgb(209 210 211);
						--inline-code-background-color: rgb(12 81 92);
						--inline-code-color: rgb(171 191 196);
						--inline-code-wrapper-border-color: var(14 97 110);
						--field-title-color: rgb(5 166 148);
						--exception-details-color: rgb(139 140 143);
						--middleware-border-color: rgb(209 210 211);
						--sensitive-parameter-value-border-color: #d81b60;
						--stacktrace-counter-color: rgb(139 140 143);
					}
				}

				.exceptionBody {
					background-color: var(--body-background-color);
					color: var(--body-color);
					margin: 0;
					padding: 0;
				}

				.exceptionContainer {
					box-sizing: border-box;
					font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
						"Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans",
						"Helvetica Neue", Arial, sans-serif;
					font-size: 15px;
					padding-bottom: 20px;
				}

				.exceptionContainer * {
					box-sizing: inherit;
					line-height: 1.5em;
					margin: 0;
					padding: 0;
				}

				.exceptionHeader {
					background-color: var(--header-background-color);
					padding: 30px 0;
				}

				.exceptionTitle {
					color: var(--title-color);
					font-size: 28px;
					font-weight: 600;
				}

				.exceptionErrorCode {
					color: var(--error-code-color);
					margin-top: .5em;
				}

				.exceptionErrorCode .exceptionInlineCode {
					background-color: var(--inline-code-background-color);
					border-radius: 3px;
					color: var(--inline-code-color);
					font-family: monospace;
					padding: 3px 10px;
					white-space: nowrap;
				}

				.exceptionSubtitle {
					font-size: 24px;
					font-weight: 600;
					margin-bottom: 10px;
				}

				.exceptionContainer>.exceptionBoundary {
					margin-top: 30px;
				}

				.exceptionText .exceptionInlineCodeWrapper {
					border: 1px solid var(--inline-code-wrapper-border-color);
					border-radius: 3px;
					padding: 2px 5px;
				}

				.exceptionText .exceptionInlineCode {
					font-family: ui-monospace, Menlo, Monaco, "Cascadia Mono",
						"Segoe UI Mono", "Roboto Mono", "Oxygen Mono", "Ubuntu Monospace", "Source Code Pro",
						"Fira Mono", "Droid Sans Mono", "Courier New", monospace;
					font-size: 14px;
					white-space: nowrap;
				}

				.exceptionFieldTitle {
					color: var(--field-title-color);
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
					font-family: ui-monospace, Menlo, Monaco, "Cascadia Mono",
						"Segoe UI Mono", "Roboto Mono", "Oxygen Mono", "Ubuntu Monospace", "Source Code Pro",
						"Fira Mono", "Droid Sans Mono", "Courier New", monospace;
					font-size: 14px;
					white-space: nowrap;
				}

				.exceptionStacktraceFile+.exceptionStacktraceCall {
					margin-top: 10px;
				}

				.exceptionFieldDetails {
					padding-left: 20px;
					word-break: break-all;
				}

				.exceptionStacktraceFile {
					padding-left: 40px;
				}

				.exceptionFieldDetails,
				.exceptionStacktraceFile {
					color: var(--exception-details-color) !important;
					font-size: 13px !important;
				}

				.exceptionStacktraceMiddleware {
					padding: 20px 0;
				}

				.exceptionStacktraceMiddleware summary {
					cursor: pointer;
					-webkit-user-select: none;
					user-select: none;
				}

				.exceptionStacktraceMiddleware ul {
					border-left: 5px solid var(--middleware-border-color);
					list-style: none;
					margin-top: 20px;
					padding-left: 15px;
				}

				.exceptionStacktraceSensitiveParameterValue {
					border: 1px dashed var(--sensitive-parameter-value-border-color);
					padding: 2px 5px;
					font-size: 12px !important;
				}

				.exceptionStacktraceCounter,
				.exceptionStacktraceType {
					color: var(--stacktrace-counter-color);
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
						max-width: 1200px;
						min-width: 800px;
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
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(phpversion()); ?></p>
							</li>
							<li class="exceptionSystemInformation3">
								<p class="exceptionFieldTitle">WoltLab Suite Core<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(WCF_VERSION); ?></p>
							</li>
							<li class="exceptionSystemInformation5">
								<p class="exceptionFieldTitle">Peak Memory Usage<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo round(memory_get_peak_usage() / 1024 / 1024, 3); ?>/<?php echo round(FileUtil::getMemoryLimit() / 1024 / 1024, 3); ?> MiB</p>
							</li>
							<li class="exceptionSystemInformation2">
								<p class="exceptionFieldTitle">Request URI<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['REQUEST_METHOD'])) echo StringUtil::encodeHTML($_SERVER['REQUEST_METHOD']); ?> <?php if (isset($_SERVER['REQUEST_URI'])) echo StringUtil::encodeHTML($_SERVER['REQUEST_URI']); ?></p>
							</li>
							<li class="exceptionSystemInformation4">
								<p class="exceptionFieldTitle">Referrer<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_REFERER'])) echo StringUtil::encodeHTML($_SERVER['HTTP_REFERER']); ?></p>
							</li>
							<li class="exceptionSystemInformation6">
								<p class="exceptionFieldTitle">User Agent<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_USER_AGENT'])) echo StringUtil::encodeHTML($_SERVER['HTTP_USER_AGENT']); ?></p>
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
								<p class="exceptionText"><?php echo StringUtil::encodeHTML($e->getDescription()); ?></p>
							<?php } ?>
							<ul class="exceptionErrorDetails">
								<li>
									<p class="exceptionFieldTitle">Error Message<span class="exceptionColon">:</span></p>
									<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML($e->getMessage()); ?></p>
								</li>
								<li>
									<p class="exceptionFieldTitle">Error Type<span class="exceptionColon">:</span></p>
									<p class="exceptionFieldValue">
										<?php echo StringUtil::encodeHTML(get_class($e)); ?>
										<?php if ($e->getCode()) { ?>
											(<?php echo StringUtil::encodeHTML($e->getCode()); ?>)
										<?php } ?>
									</p>
									<p class="exceptionFieldDetails"><?php echo formatPath(sanitizePath($e->getFile(), false), $e->getLine()); ?></p>
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
											<p class="exceptionFieldTitle"><?php echo StringUtil::encodeHTML($key); ?><span class="exceptionColon">:</span></p>
											<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML($value); ?></p>
										</li>
									<?php
									}
								}

								$templateContextLines = getTemplateContextLines($e);
								if (!empty($templateContextLines)) {
									?>
									<li>
										<p class="exceptionFieldTitle">Template Context<span class="exceptionColon">:</span></p>
										<pre class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(implode("", $templateContextLines)); ?></pre>
									</li>
								<?php
								}
								?>
							</ul>
						</div>
						<div class="exceptionBoundary">
							<p class="exceptionSubtitle">Stack Trace</p>
							<ul class="exceptionStacktrace">
								<?php
								$trace = sanitizeStacktrace($e);
								$foundMiddlewareEnd = false;
								for ($i = 0, $max = count($trace); $i < $max; $i++) {
									// The stacktrace is in reverse order, therefore we need to check for
									// the end of the middleware first.
									if (isMiddlewareEnd($trace[$i])) {
										$foundMiddlewareEnd = true;
								?>
										<li class="exceptionStacktraceMiddleware">
											<details>
												<summary>Middleware</summary>
												<ul>
												<?php
											} elseif (isMiddlewareStart($trace[$i]) && $foundMiddlewareEnd) {
												?>
												</ul>
											</details>
										</li>
									<?php
											}
									?>
									<li class="exceptionStacktraceCall">
										<span class="exceptionStacktraceCounter">#<?php echo $i; ?></span>
										<?php
										echo \sprintf(
											'<strong>%s</strong><span class="exceptionStacktraceType">%s</span>%s(',
											$trace[$i]['class'],
											$trace[$i]['type'],
											$trace[$i]['function'],
										);
										echo implode(', ', array_map(function ($item) {
											switch (gettype($item)) {
												case 'integer':
												case 'double':
													return $item;
												case 'NULL':
													return 'null';
												case 'string':
													return "'" . StringUtil::encodeHTML(addcslashes($item, "\\'")) . "'";
												case 'boolean':
													return $item ? 'true' : 'false';
												case 'array':
													$keys = array_keys($item);
													if (count($keys) > 5) return "[ " . count($keys) . " items ]";
													return '[ ' . implode(', ', array_map(function ($item) {
														return $item . ' => ';
													}, $keys)) . ']';
												case 'object':
													if ($item instanceof \UnitEnum) {
														return $item::class . '::' . $item->name;
													}
													if ($item instanceof \SensitiveParameterValue) {
														return '<span class="exceptionStacktraceSensitiveParameterValue">' . $item::class . '</span>';
													}

													return $item::class;
												case 'resource':
													return 'resource(' . get_resource_type($item) . ')';
												case 'resource (closed)':
													return 'resource (closed)';
											}

											throw new \LogicException('Unreachable');
										}, $trace[$i]['args']));
										echo ')</li>';
										?>
									<li class="exceptionStacktraceFile"><?php echo StringUtil::encodeHTML($trace[$i]['file']) . ':' . $trace[$i]['line']; ?></li>
								<?php
								}
								?>
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

	/**
	 * Returns the stack trace of the given Throwable with sensitive
	 * information removed.
	 *
	 * @param	bool			$ignorePaths	If set to `true`: Don't call `sanitizePath`.
	 * @return	mixed[]
	 */
	function sanitizeStacktrace(\Throwable $e, bool $ignorePaths = false)
	{
		$trace = getTraceWithoutIntermediateMiddleware($e);

		return array_map(function ($item) use ($ignorePaths) {
			if (!isset($item['file'])) $item['file'] = '[internal function]';
			if (!isset($item['line'])) $item['line'] = '?';
			if (!isset($item['class'])) $item['class'] = '';
			if (!isset($item['type'])) $item['type'] = '';
			if (!isset($item['args'])) $item['args'] = [];

			try {
				$cannotBeReflected = !$item['class'] && \in_array($item['function'], [
					'include',
					'include_once',
					'require',
					'require_once',
				]);

				if (!empty($item['args']) && !$cannotBeReflected) {
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

						if (
							$isSensitive
							&& isset($item['args'][$i])
							&& !($item['args'][$i] instanceof \SensitiveParameterValue)
						) {
							$item['args'][$i] = new \SensitiveParameterValue($item['args'][$i]);
						}
						$i++;
					}

					// strip database credentials
					if (
						preg_match('~\\\\?wcf\\\\system\\\\database\\\\[a-zA-Z]*Database~', $item['class'])
						|| $item['class'] === 'PDO'
					) {
						if ($item['function'] === '__construct') {
							$item['args'] = array_map(function ($value) {
								if (!($value instanceof \SensitiveParameterValue)) {
									$value = new \SensitiveParameterValue($value);
								}

								return $value;
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

	/**
	 * Suppresses stack frames from the middleware unless the exception occurred
	 * inside a middleware. This massively cleans up the stack trace which has
	 * seen ratios of >80% frames originating from the middleware.
	 *
	 * This has the downside that the middleware is less transparent but they simply
	 * rendered stack traces, especially those pasted into messages, unreadable.
	 * In particular wrapped exceptions could yield massive stack traces.
	 */
	function getTraceWithoutIntermediateMiddleware(\Throwable $e): array
	{
		$trace = $e->getTrace();
		if (\str_contains($trace[0]['class'] ?? '', '\\http\\middleware\\')) {
			return $trace;
		}

		$insideMiddleware = false;
		return \array_values(
			\array_filter($trace, function ($item) use (&$insideMiddleware) {
				if (isMiddlewareEnd($item)) {
					$insideMiddleware = true;
				} else if (isMiddlewareStart($item)) {
					$insideMiddleware = false;
				} else if ($insideMiddleware) {
					return false;
				}

				return true;
			})
		);
	}

	/**
	 * Returns the given path relative to `WCF_DIR`, unless both,
	 * `EXCEPTION_PRIVACY` is `public` and the debug mode is enabled.
	 */
	function sanitizePath(string $path, bool $removePath = true): string
	{
		if ($path === '[internal function]') {
			return $path;
		}

		if (!$removePath && WCF::debugModeIsEnabled() && defined('EXCEPTION_PRIVACY') && EXCEPTION_PRIVACY === 'public') {
			return $path;
		}

		return '*/' . FileUtil::removeTrailingSlash(FileUtil::getRelativePath(WCF_DIR, $path));
	}

	function formatPath(string $path, int $lineNumber): string
	{
		$path = FileUtil::unifyDirSeparator($path);
		[
			'dirname' => $dirname,
			'basename' => $basename
		] = \pathinfo($path);

		return \sprintf(
			'%s/<strong>%s</strong>:<strong>%s</strong>',
			StringUtil::encodeHTML($dirname),
			StringUtil::encodeHTML($basename),
			$lineNumber,
		);
	}

	function isMiddlewareStart(array $segment): bool
	{
		if (!isset($segment['class'])) {
			return false;
		}

		return $segment['class'] === Pipeline::class && $segment['function'] === 'process';
	}

	function isMiddlewareEnd(array $segment): bool
	{
		if (!isset($segment['class'])) {
			return false;
		}

		return $segment['class'] === Request::class && $segment['function'] === 'handle';
	}
}

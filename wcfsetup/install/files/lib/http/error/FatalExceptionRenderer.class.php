<?php

namespace wcf\http\error;

use wcf\system\exception\IExtraInformationException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StacktraceUtil;
use wcf\util\StringUtil;

/**
 * Renders a full HTML page for fatal/uncaught exceptions.
 *
 * @author  	Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license 	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   	6.3
 */
final class FatalExceptionRenderer
{
    /**
     * Pretty prints the given Throwable. It is recommended to `exit;`
     * the request after calling this method.
     *
     * @throws \Exception
     */
    public static function render(\Throwable $e): void
    {
        $exceptionID = ExceptionLogger::log($e, $logFile);
        $exceptionTitle = $exceptionSubtitle = $exceptionExplanation = '';
        $logFile = StacktraceUtil::sanitizePath($logFile);
        try {
            // @phpstan-ignore notIdentical.alwaysTrue
            if (WCF::getLanguage() !== null) {
                $exceptionTitle = WCF::getLanguage()->get('wcf.global.exception.title', true);
                $exceptionSubtitle = \str_replace('{$exceptionID}', $exceptionID, WCF::getLanguage()->get('wcf.global.exception.subtitle', true));
                $exceptionExplanation = \str_replace('{$logFile}', $logFile, WCF::getLanguage()->get('wcf.global.exception.explanation', true));
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
	The error code can be used by an administrator to lookup the full error message in the Administration Control Panel via "Logs » Errors".
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
			<?php if (!\defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
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
						<p class="exceptionErrorCode"><?php echo \str_replace('{$exceptionID}', $exceptionID, $exceptionSubtitle); ?></p>
					</div>
				</div>

				<div class="exceptionBoundary">
					<?php echo $exceptionExplanation; ?>
				</div>
				<?php if (!\defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
					<div class="exceptionBoundary">
						<p class="exceptionSubtitle">System Information</p>
						<ul class="exceptionSystemInformation">
							<li class="exceptionSystemInformation1">
								<p class="exceptionFieldTitle">PHP Version<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(\phpversion()); ?></p>
							</li>
							<li class="exceptionSystemInformation3">
								<p class="exceptionFieldTitle">WoltLab Suite Core<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(WCF_VERSION); ?></p>
							</li>
							<li class="exceptionSystemInformation5">
								<p class="exceptionFieldTitle">Peak Memory Usage<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo \round(\memory_get_peak_usage() / 1024 / 1024, 3); ?>/<?php echo \round(FileUtil::getMemoryLimit() / 1024 / 1024, 3); ?> MiB</p>
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

					$e = \array_pop($exceptions);
					do {
					?>
						<div class="exceptionBoundary">
							<p class="exceptionSubtitle"><?php if (!empty($exceptions) && $first) {
																echo "Original ";
															} elseif (empty($exceptions) && !$first) {
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
										<?php echo StringUtil::encodeHTML(\get_class($e)); ?>
										<?php if ($e->getCode()) { ?>
											(<?php echo StringUtil::encodeHTML($e->getCode()); ?>)
										<?php } ?>
									</p>
									<p class="exceptionFieldDetails"><?php echo StacktraceUtil::formatPath(StacktraceUtil::sanitizePath($e->getFile(), false), $e->getLine()); ?></p>
								</li>

								<?php
								if ($e instanceof SystemException) {
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

								$templateContextLines = StacktraceUtil::getTemplateContextLines($e);
								if (!empty($templateContextLines)) {
									?>
									<li>
										<p class="exceptionFieldTitle">Template Context<span class="exceptionColon">:</span></p>
										<pre class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(\implode("", $templateContextLines)); ?></pre>
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
								$trace = StacktraceUtil::sanitize($e);
								$foundMiddlewareEnd = false;
								for ($i = 0, $max = \count($trace); $i < $max; $i++) {
									// The stacktrace is in reverse order, therefore we need to check for
									// the end of the middleware first.
									if (StacktraceUtil::isMiddlewareEnd($trace[$i])) {
										$foundMiddlewareEnd = true;
								?>
										<li class="exceptionStacktraceMiddleware">
											<details>
												<summary>Middleware</summary>
												<ul>
												<?php
											} elseif (StacktraceUtil::isMiddlewareStart($trace[$i]) && $foundMiddlewareEnd) {
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
										echo \implode(', ', \array_map(static function ($item) {
											switch (\gettype($item)) {
												case 'integer':
												case 'double':
													return $item;
												case 'NULL':
													return 'null';
												case 'string':
													return "'" . StringUtil::encodeHTML(\addcslashes($item, "\\'")) . "'";
												case 'boolean':
													return $item ? 'true' : 'false';
												case 'array':
													$keys = \array_keys($item);
													if (\count($keys) > 5) return "[ " . \count($keys) . " items ]";
													return '[ ' . \implode(', ', \array_map(static function ($item) {
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
													return 'resource(' . \get_resource_type($item) . ')';
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
					} while ($e = \array_pop($exceptions));
					?>
				<?php } ?>
			</div>
		</body>

		</html>
<?php
    }

	private function __construct() {}
}

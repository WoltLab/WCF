<?php

namespace wcf\util;

use wcf\http\Pipeline;
use wcf\system\request\Request;
use wcf\system\WCF;

/**
 * Provides utility methods for processing and sanitizing stack traces.
 *
 * @author      Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class StacktraceUtil
{
    /**
     * If the stacktrace contains a compiled template, the context of the relevant template line
     * is returned, otherwise an empty array is returned.
     *
     * @return string[]
     */
    public static function getTemplateContextLines(\Throwable $e): array
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
     * Returns the stack trace of the given Throwable with sensitive
     * information removed.
     *
     * @param   bool    $ignorePaths    If set to `true`: Don't call `sanitizePath`.
     * @return  mixed[]
     */
    public static function sanitize(\Throwable $e, bool $ignorePaths = false): array
    {
        $trace = self::getTraceWithoutIntermediateMiddleware($e);

        return \array_map(static function ($item) use ($ignorePaths) {
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
                        \preg_match('~\\\\?wcf\\\\system\\\\database\\\\[a-zA-Z]*Database~', $item['class'])
                        || $item['class'] === 'PDO'
                    ) {
                        if ($item['function'] === '__construct') {
                            $item['args'] = \array_map(static function ($value) {
                                if (!($value instanceof \SensitiveParameterValue)) {
                                    $value = new \SensitiveParameterValue($value);
                                }

                                return $value;
                            }, $item['args']);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $item['args'] = \array_map(static function () {
                    return '[error_during_sanitization]';
                }, $item['args']);
            }

            if (!$ignorePaths) {
                $item['args'] = \array_map(static function ($item) {
                    if (!\is_string($item)) return $item;

                    if (\preg_match('~^(' . \preg_quote($_SERVER['DOCUMENT_ROOT'], '~') . '|' . \preg_quote(WCF_DIR, '~') . ')~', $item)) {
                        $item = self::sanitizePath($item);
                    }

                    return $item;
                }, $item['args']);

                $item['file'] = self::sanitizePath($item['file']);
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
     *
     * @return mixed[]
     */
    public static function getTraceWithoutIntermediateMiddleware(\Throwable $e): array
    {
        $trace = $e->getTrace();
        if (\str_contains($trace[0]['class'] ?? '', '\\http\\middleware\\')) {
            return $trace;
        }

        $insideMiddleware = false;
        return \array_values(
            \array_filter($trace, static function ($item) use (&$insideMiddleware) {
                if (self::isMiddlewareEnd($item)) {
                    $insideMiddleware = true;
                } elseif (self::isMiddlewareStart($item)) {
                    $insideMiddleware = false;
                } elseif ($insideMiddleware) {
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
    public static function sanitizePath(string $path, bool $removePath = true): string
    {
        if ($path === '[internal function]') {
            return $path;
        }

        if (!$removePath && WCF::debugModeIsEnabled() && \defined('EXCEPTION_PRIVACY') && EXCEPTION_PRIVACY === 'public') {
            return $path;
        }

        return '*/' . FileUtil::removeTrailingSlash(FileUtil::getRelativePath(WCF_DIR, $path));
    }

    public static function formatPath(string $path, int $lineNumber): string
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

    /**
     * @param mixed[] $segment
     */
    public static function isMiddlewareStart(array $segment): bool
    {
        if (!isset($segment['class'])) {
            return false;
        }

        return $segment['class'] === Pipeline::class && $segment['function'] === 'process';
    }

    /**
     * @param mixed[] $segment
     */
    public static function isMiddlewareEnd(array $segment): bool
    {
        if (!isset($segment['class'])) {
            return false;
        }

        return $segment['class'] === Request::class && $segment['function'] === 'handle';
    }

    private function __construct() {}
}

<?php

namespace wcf\http\error;

use wcf\system\exception\IExtraInformationException;
use wcf\util\FileUtil;
use wcf\util\StacktraceUtil;

/**
 * Logs exceptions to the daily log file.
 *
 * @author      Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ExceptionLogger
{
    /**
     * Logs the given Throwable.
     *
     * @param   string  $logFile    The log file to use. If set to `null` the default log file will be used and the variable contents will be replaced by the actual path.
     * @param-out string $logFile
     * @return  string              The ID of the log entry.
     */
    public static function log(\Throwable $e, ?string &$logFile = null): string
    {
        if ($logFile === null) $logFile = WCF_DIR . 'log/' . \gmdate('Y-m-d', TIME_NOW) . '.txt';
        \touch($logFile);

        $stripNewlines = static function ($item) {
            return \str_replace("\n", ' ', $item);
        };

        $getExtraInformation = static function (\Throwable $e) {
            $extraInformation = [];

            if ($e instanceof IExtraInformationException) {
                $extraInformation = $e->getExtraInformation();
            }

            $templateContextLines = StacktraceUtil::getTemplateContextLines($e);
            if (!empty($templateContextLines)) {
                $extraInformation[] = [
                    'Template Context',
                    \implode("", $templateContextLines),
                ];
            }

            return !empty($extraInformation) ? \base64_encode(\serialize($extraInformation)) : "-";
        };

        // don't forget to update ExceptionLogUtil / ExceptionLogViewPage, when changing the log file format
        $message = \gmdate('r', TIME_NOW) . "\n" .
            'Message: ' . $stripNewlines($e->getMessage()) . "\n" .
            'PHP version: ' . \phpversion() . "\n" .
            'WoltLab Suite version: ' . WCF_VERSION . "\n" .
            'Request URI: ' . $stripNewlines(($_SERVER['REQUEST_METHOD'] ?? '') . ' ' . ($_SERVER['REQUEST_URI'] ?? '')) . (\wcf\getRequestId() ? ' (' . \wcf\getRequestId() . ')' : '') . "\n" .
            'Referrer: ' . $stripNewlines($_SERVER['HTTP_REFERER'] ?? '') . "\n" .
            'User Agent: ' . $stripNewlines($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n" .
            'Peak Memory Usage: ' . \memory_get_peak_usage() . '/' . FileUtil::getMemoryLimit() . "\n";
        $prev = $e;
        do {
            $message .= "======\n" .
                'Error Class: ' . \get_class($prev) . "\n" .
                'Error Message: ' . $stripNewlines($prev->getMessage()) . "\n" .
                'Error Code: ' . $stripNewlines($prev->getCode()) . "\n" .
                'File: ' . $stripNewlines($prev->getFile()) . ' (' . $prev->getLine() . ')' . "\n" .
                'Extra Information: ' . $getExtraInformation($prev) . "\n" .
                'Stack Trace: ' . \json_encode(\array_map(static function ($item) {
                    $item['args'] = \array_map(static function ($item) {
                        switch (\gettype($item)) {
                            case 'object':
                                if ($item instanceof \UnitEnum) {
                                    return $item::class . '::' . $item->name;
                                }

                                return $item::class;
                            case 'array':
                                return \array_map(static function () {
                                    return '[redacted]';
                                }, $item);
                            case 'resource':
                                return 'resource(' . \get_resource_type($item) . ')';
                            default:
                                return $item;
                        }
                    }, $item['args']);

                    return $item;
                }, StacktraceUtil::sanitize($prev, true))) . "\n";
        } while ($prev = $prev->getPrevious());

        // calculate Exception-ID
        $exceptionID = \sha1($message);
        $entry = "<<<<<<<<" . $exceptionID . "<<<<\n" . $message . "<<<<\n\n";

        \file_put_contents($logFile, $entry, \FILE_APPEND);

        return $exceptionID;
    }

    private function __construct() {}
}

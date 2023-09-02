<?php

namespace wcf\system\exception;

use Throwable;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * AJAXException provides JSON-encoded exceptions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AJAXException extends LoggedException
{
    /**
     * missing parameters
     * @var int
     */
    const MISSING_PARAMETERS = 400;

    /**
     * session expired
     * @var int
     */
    const SESSION_EXPIRED = 401;

    /**
     * insufficient permissions
     * @var int
     */
    const INSUFFICIENT_PERMISSIONS = 403;

    /**
     * illegal link
     * @var int
     */
    const ILLEGAL_LINK = 404;

    /**
     * bad parameters
     * @var int
     */
    const BAD_PARAMETERS = 412;

    /**
     * internal server error
     * @var int
     */
    const INTERNAL_ERROR = 503;

    /**
     * Throws a JSON-encoded error message
     *
     * @param string $message
     * @param int $errorType
     * @param string $stacktrace
     * @param mixed[] $returnValues
     * @param string $exceptionID
     * @param \Exception|\Throwable $previous
     */
    public function __construct(
        $message,
        $errorType = self::INTERNAL_ERROR,
        $stacktrace = null,
        $returnValues = [],
        $exceptionID = '',
        $previous = null,
        array $extraInformation = [],
    ) {
        if ($stacktrace === null) {
            $stacktrace = self::getSanitizedTraceAsString($this);
        }

        // include a stacktrace if:
        // - debug mode is enabled or
        // - within ACP and a SystemException was thrown
        $includeStacktrace = false;
        if (\class_exists(WCFACP::class, false)) {
            // ACP
            if (WCF::debugModeIsEnabled(true) || $errorType === self::INTERNAL_ERROR) {
                $includeStacktrace = true;
            }
        } else {
            // frontend
            $includeStacktrace = WCF::debugModeIsEnabled();
        }

        // extract file and line in which exception was thrown and only include it
        // if stacktrace is also included
        $file = $line = null;
        if (isset($returnValues['file'])) {
            if ($includeStacktrace) {
                $file = $returnValues['file'];
            }

            unset($returnValues['file']);
        }
        if (isset($returnValues['line'])) {
            if ($includeStacktrace) {
                $line = $returnValues['line'];
            }

            unset($returnValues['line']);
        }

        $responseData = [
            'code' => $errorType,
            'file' => $file,
            'line' => $line,
            'extraInformation' => $extraInformation,
            'message' => $message,
            'previous' => [],
            'returnValues' => $returnValues,
        ];

        if ($includeStacktrace) {
            $responseData['stacktrace'] = $stacktrace;

            while ($previous) {
                $data = ['message' => $previous->getMessage()];
                $data['stacktrace'] = self::getSanitizedTraceAsString($previous);

                $responseData['previous'][] = $data;
                $previous = $previous->getPrevious();
            }
        }

        $statusHeader = '';
        switch ($errorType) {
            case self::MISSING_PARAMETERS:
                $statusHeader = 'HTTP/1.1 400 Bad Request';

                $responseData['exceptionID'] = $exceptionID;
                $responseData['message'] = WCF::getLanguage()->get('wcf.ajax.error.badRequest');
                break;

            case self::SESSION_EXPIRED:
                $statusHeader = 'HTTP/1.1 409 Conflict';
                break;

            case self::INSUFFICIENT_PERMISSIONS:
                $statusHeader = 'HTTP/1.1 403 Forbidden';
                break;

            case self::BAD_PARAMETERS:
                $statusHeader = 'HTTP/1.1 400 Bad Request';

                $responseData['exceptionID'] = $exceptionID;
                break;

            default:
            case self::ILLEGAL_LINK:
            case self::INTERNAL_ERROR:
                //header('HTTP/1.1 418 I\'m a Teapot');
                \header('HTTP/1.1 503 Service Unavailable');

                $responseData['code'] = self::INTERNAL_ERROR;
                $responseData['exceptionID'] = $exceptionID;
                if (!WCF::debugModeIsEnabled()) {
                    $responseData['message'] = WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.internalError');
                }
                break;
        }

        \header($statusHeader);
        \header('Content-type: application/json; charset=UTF-8');
        echo JSON::encode($responseData);

        exit;
    }

    /**
     * @since 6.0
     */
    public static function getSanitizedTraceAsString(\Throwable $e): string
    {
        $trace = \wcf\functions\exception\sanitizeStacktrace($e);

        $length = \count($trace);
        $maxWidth = \strlen((string)$length) + 1;
        for ($i = 0, $length = \count($trace); $i < $length; $i++) {
            $item = $trace[$i];

            $trace[$i] = \sprintf(
                '%s %s%s%s(%s)',
                \str_pad("#{$i}", $maxWidth, ' ', \STR_PAD_LEFT),
                $item['class'],
                $item['type'],
                $item['function'],
                \implode(', ', \array_map(
                    static function ($item) {
                        switch (\gettype($item)) {
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
                    },
                    $item['args']
                )),
            );
        }

        return \implode("\n", $trace);
    }
}

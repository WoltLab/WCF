<?php

namespace wcf\util;

use wcf\system\exception\SystemException;

/**
 * Provides methods for JSON.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.2 Use `\json_encode($data, \JSON_THROW_ON_ERROR)` and `\json_decode($json, true, flags: \JSON_THROW_ON_ERROR)` instead.
 */
final class JSON
{
    /**
     * Returns the JSON representation of a value.
     *
     * @deprecated 6.2 Use `\json_encode($data, \JSON_THROW_ON_ERROR)` instead.
     */
    public static function encode(mixed $data, int $options = 0): string
    {
        return \json_encode($data, $options);
    }

    /**
     * Decodes a JSON string.
     *
     * @return ($asArray is true ? mixed[] : \stdClass)
     * @throws  SystemException
     * @deprecated 6.2 Use `\json_decode($json, true, flags: \JSON_THROW_ON_ERROR)` instead.
     */
    public static function decode(string $json, bool $asArray = true): array|\stdClass
    {
        $data = @\json_decode($json, $asArray);

        if ($data === null && self::getLastError() !== \JSON_ERROR_NONE) {
            throw new SystemException(\sprintf(
                'Could not decode JSON (error %d): %s',
                self::getLastError(),
                StringUtil::truncate($json, 250, StringUtil::HELLIP, true)
            ));
        }

        return $data;
    }

    /**
     * Returns the last error occurred.
     */
    public static function getLastError(): int
    {
        return \json_last_error();
    }

    /**
     * Forbid creation of JSON objects.
     */
    private function __construct()
    {
        // does nothing
    }
}

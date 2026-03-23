<?php

namespace wcf\system\message\embedded\object;

/**
 * Default interface of simple embedded object handler.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ISimpleMessageEmbeddedObjectHandler extends IMessageEmbeddedObjectHandler
{
    /**
     * Validates the provided values for existence and returns the filtered list.
     *
     * @param int[] $values list of value ids
     * @return int[] filtered list
     */
    public function validateValues(string $objectType, int $objectID, array $values);

    /**
     * Returns replacement string for simple placeholders. Must return `null`
     * if no replacement should be performed due to invalid or missing arguments.
     *
     * @param array<string, string> $attributes list of additional attributes
     * @return ?string replacement string or null if value id is unknown
     */
    public function replaceSimple(string $objectType, int $objectID, string|int $value, array $attributes);
}

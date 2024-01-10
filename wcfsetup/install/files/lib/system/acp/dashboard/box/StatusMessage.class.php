<?php

namespace wcf\system\acp\dashboard\box;

/**
 * Represents a status message.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class StatusMessage
{
    public function __construct(public readonly StatusMessageType $type, public readonly string $message)
    {
    }
}

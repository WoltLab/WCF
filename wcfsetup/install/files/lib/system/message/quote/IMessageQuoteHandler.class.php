<?php

namespace wcf\system\message\quote;

use wcf\data\IMessage;

/**
 * Default interface for quote handlers.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IMessageQuoteHandler
{
    /**
     * Returns the message identified by the provided object id.
     *
     * If the object does not exist or is inaccessible by the current user,
     * `null` must be returned instead.
     *
     * @since 6.2
     */
    public function getMessage(int $objectID): ?IMessage;
}

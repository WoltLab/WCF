<?php

namespace wcf\data\user\notification\event;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user notification events.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static UserNotificationEvent   create(array $parameters = [])
 * @method      UserNotificationEvent   getDecoratedObject()
 * @mixin       UserNotificationEvent
 */
class UserNotificationEventEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserNotificationEvent::class;
}

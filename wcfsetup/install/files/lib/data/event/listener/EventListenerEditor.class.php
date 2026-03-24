<?php

namespace wcf\data\event\listener;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\EventListenerCacheBuilder;

/**
 * Provides functions to edit event listener.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       EventListener
 * @extends DatabaseObjectEditor<EventListener>
 * @implements IEditableCachedObject<EventListener>
 */
class EventListenerEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = EventListener::class;

    /**
     * @since   5.2
     */
    #[\Override]
    public static function resetCache()
    {
        EventListenerCacheBuilder::getInstance()->reset();
    }
}

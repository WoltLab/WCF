<?php

namespace wcf\data\event\listener;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes event listener-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  EventListener       create()
 * @method  EventListenerEditor[]   getObjects()
 * @method  EventListenerEditor getSingleObject()
 */
class EventListenerAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = EventListenerEditor::class;
}

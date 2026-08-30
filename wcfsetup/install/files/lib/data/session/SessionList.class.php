<?php

namespace wcf\data\session;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of legacy sessions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template-covariant TDatabaseObject of Session|DatabaseObjectDecorator = Session
 * @extends DatabaseObjectList<TDatabaseObject>
 */
class SessionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Session::class;
}

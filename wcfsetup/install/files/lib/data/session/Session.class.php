<?php

namespace wcf\data\session;

use wcf\data\acp\session\ACPSession;

/**
 * Represents a session.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int|null $pageID         id of the latest page visited
 * @property-read   int|null $pageObjectID       id of the object the latest page visited belongs to
 * @property-read   int|null $parentPageID       id of the parent page of latest page visited
 * @property-read   int|null $parentPageObjectID id of the object the parent page of latest page visited belongs to
 * @property-read   ?string $spiderIdentifier identifier of the spider
 */
class Session extends ACPSession
{
    /**
     * @inheritDoc
     */
    public static function supportsPersistentLogins()
    {
        return true;
    }
}

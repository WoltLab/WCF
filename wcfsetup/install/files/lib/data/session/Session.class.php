<?php

namespace wcf\data\session;

use wcf\data\DatabaseObject;

/**
 * Represents a legacy session.
 *
 * Legacy sessions are only used for the online user list. The actual sessions are managed by `SessionHandler`.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   string  $sessionID          unique textual identifier of the session
 * @property-read   ?int    $userID             id of the user the session belongs to or `null` if the session belongs to a guest
 * @property-read   string  $ipAddress          id of the user whom the session belongs to
 * @property-read   string  $userAgent          user agent of the user whom the session belongs to
 * @property-read   int     $lastActivityTime   timestamp at which the latest activity occurred
 * @property-read   string  $requestURI         uri of the latest request
 * @property-read   string  $requestMethod      used request method of the latest request (`GET`, `POST`)
 * @property-read   ?int    $pageID             id of the latest page visited
 * @property-read   ?int    $pageObjectID       id of the object the latest page visited belongs to
 * @property-read   ?int    $parentPageID       id of the parent page of latest page visited
 * @property-read   ?int    $parentPageObjectID id of the object the parent page of latest page visited belongs to
 * @property-read   ?string $spiderIdentifier   identifier of the spider
 */
class Session extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexIsIdentity = false;
}

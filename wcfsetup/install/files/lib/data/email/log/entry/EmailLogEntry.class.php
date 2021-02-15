<?php

namespace wcf\data\email\log\entry;

use wcf\data\DatabaseObject;

/**
 * Represents an email log entry.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Email\Log\Entry
 *
 * @property-read   int    $entryID      unique id of the log entry
 * @property-read   int    $time         timestamp when the delivery job was created
 * @property-read   string $messageID    the email's 'Message-ID'
 * @property-read   string $recipient    the recipient ("RCPT TO")
 * @property-read   ?int   $recipientID  the recipient's userID (if the email is being sent to a registered user)
 * @property-read   string $status       one of the `STATUS_*` constants
 * @property-read   string $message      a human readable explanation for the status
 *
 */
class EmailLogEntry extends DatabaseObject
{
    public const STATUS_NEW = 'new';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_TRANSIENT_FAILURE = 'transient_failure';

    public const STATUS_PERMANENT_FAILURE = 'permanent_failure';
}

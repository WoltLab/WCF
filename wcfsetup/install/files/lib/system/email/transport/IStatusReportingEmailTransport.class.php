<?php

namespace wcf\system\email\transport;

use wcf\system\email\Email;
use wcf\system\email\Mailbox;

/**
 * An IStatusReportingEmailTransport returns a status message from deliver().
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Email\Transport
 * @since   5.4
 */
interface IStatusReportingEmailTransport extends IEmailTransport
{
    /**
     * @inheritDoc
     */
    public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo): string;
}

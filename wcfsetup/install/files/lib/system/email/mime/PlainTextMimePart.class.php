<?php

namespace wcf\system\email\mime;

/**
 * PlainTextMimePart is a text/plain implementation of a RecipientAwareTextMimePart.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PlainTextMimePart extends RecipientAwareTextMimePart
{
    /**
     * @param string $content Content of this text part.
     */
    public function __construct(string $content)
    {
        parent::__construct('text/plain', 'email_plaintext', 'wcf', $content);
    }
}

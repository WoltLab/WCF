<?php

namespace wcf\system\email\mime;

/**
 * HtmlTextMimePart is a text/html implementation of a RecipientAwareTextMimePart.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlTextMimePart extends RecipientAwareTextMimePart
{
    /**
     * @param string $content Content of this text part.
     */
    public function __construct(string $content)
    {
        parent::__construct('text/html', 'email_html', 'wcf', $content);
    }
}

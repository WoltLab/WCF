<?php

namespace wcf\system\page\handler;

/**
 * Hides the contact form if no recipients are enabled.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 * @deprecated 6.2 No longer in use.
 */
class ContactFormPageHandler extends AbstractMenuPageHandler
{
    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        return true;
    }
}

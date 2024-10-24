<?php

namespace wcf\system\page\handler;

use wcf\system\WCF;

/**
 * Hides the contact form if no recipients are enabled.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
class ContactFormPageHandler extends AbstractMenuPageHandler
{
    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        $sql = "SELECT EXISTS(
                    SELECT  *
                    FROM    wcf1_contact_recipient
                    WHERE   isDisabled = ?
                )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([0]);

        return $statement->fetchSingleColumn();
    }
}

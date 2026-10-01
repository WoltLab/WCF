<?php

namespace wcf\data\contact\recipient;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit contact recipients.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       ContactRecipient
 * @extends DatabaseObjectEditor<ContactRecipient>
 * @deprecated 6.3 use `ContactRecipientBuilder` instead.
 */
class ContactRecipientEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ContactRecipient::class;
}

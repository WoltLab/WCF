<?php

namespace wcf\data\contact\recipient;

use wcf\system\l10n\L10nStorage;

/**
 * List of contact recipients with localized name and email values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nContactRecipientList extends ContactRecipientList
{
    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(ContactRecipient::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('name', $this->getDatabaseTableAlias())
            . ' AS name, '
            . $storage->getSubSelect('email', $this->getDatabaseTableAlias())
            . ' AS email';
    }
}

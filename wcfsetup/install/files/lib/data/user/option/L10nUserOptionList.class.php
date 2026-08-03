<?php

namespace wcf\data\user\option;

use wcf\system\l10n\L10nStorage;

/**
 * List of user options with localized title values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nUserOptionList extends UserOptionList
{
    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(UserOption::getL10nDefinition());

        $this->sqlSelects .= (!empty($this->sqlSelects) ? ', ' : '')
            . $storage->getSubSelect('title', $this->getDatabaseTableAlias())
            . ' AS title';
    }
}

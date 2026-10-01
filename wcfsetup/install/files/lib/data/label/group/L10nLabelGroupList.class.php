<?php

namespace wcf\data\label\group;

use wcf\system\l10n\L10nStorage;

/**
 * List of label groups with localized title values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nLabelGroupList extends LabelGroupList
{
    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(LabelGroup::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('groupName', $this->getDatabaseTableAlias())
            . ' AS groupName';
    }
}

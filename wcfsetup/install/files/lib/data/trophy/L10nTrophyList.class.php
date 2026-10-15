<?php

namespace wcf\data\trophy;

use wcf\system\l10n\L10nStorage;

/**
 * List of trophies with localized title and description values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nTrophyList extends TrophyList
{
    /**
     * @inheritDoc
     */
    public $className = Trophy::class;

    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(Trophy::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('title', $this->getDatabaseTableAlias())
            . ' AS title, '
            . $storage->getSubSelect('description', $this->getDatabaseTableAlias())
            . ' AS description';
    }
}

<?php

namespace wcf\data\user\rank;

use wcf\system\l10n\L10nStorage;

/**
 * List of user ranks with localized title values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nUserRankList extends UserRankList
{
    /**
     * @inheritDoc
     */
    public $className = UserRank::class;

    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(UserRank::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('rankTitle', $this->getDatabaseTableAlias())
            . ' AS rankTitle';
    }
}

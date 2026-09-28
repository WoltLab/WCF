<?php

namespace wcf\data\reaction\type;

use wcf\system\l10n\L10nStorage;

/**
 * List of reaction types with localized title values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nReactionTypeList extends ReactionTypeList
{
    /**
     * @inheritDoc
     */
    public $className = ReactionType::class;

    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(ReactionType::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('title', $this->getDatabaseTableAlias())
            . ' AS title';
    }
}

<?php

namespace wcf\data\cronjob;

use wcf\system\l10n\L10nStorage;

/**
 * List of cronjobs with localized description values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nCronjobList extends CronjobList
{
    /**
     * @inheritDoc
     */
    public $className = Cronjob::class;

    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(Cronjob::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('description', $this->getDatabaseTableAlias())
            . ' AS description';
    }
}

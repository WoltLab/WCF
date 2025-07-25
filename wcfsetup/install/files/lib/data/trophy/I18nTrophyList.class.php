<?php

namespace wcf\data\trophy;

use wcf\data\I18nDatabaseObjectList;

/**
 * Represents a trophy list.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends I18nDatabaseObjectList<Trophy>
 */
class I18nTrophyList extends I18nDatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $i18nFields = ['title' => 'titleI18n'];

    /**
     * @inheritDoc
     */
    public $className = Trophy::class;
}

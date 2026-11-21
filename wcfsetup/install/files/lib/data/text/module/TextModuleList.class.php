<?php

namespace wcf\data\text\module;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of text modules.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectList<TextModule>
 */
final class TextModuleList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'text_module.title ASC';
}

<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;

/**
 * Formats the content of a column as an object id.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class ObjectIdColumnRenderer extends AbstractColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return \intval($value);
    }

    #[\Override]
    public function getClasses(): string
    {
        return 'gridView__column--digits';
    }
}

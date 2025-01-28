<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;

/**
 * Provides an abstract implementation of a column renderer.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractColumnRenderer implements IColumnRenderer
{
    #[\Override]
    public function getClasses(): string
    {
        return '';
    }

    #[\Override]
    public function prepare(mixed $value, DatabaseObject $row): void {}
}

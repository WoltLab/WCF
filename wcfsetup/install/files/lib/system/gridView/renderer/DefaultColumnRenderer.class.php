<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\util\StringUtil;

/**
 * The default column renderer is automatically applied to all columns if no other renderers have been set.
 * It converts special characters to HTML entities.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class DefaultColumnRenderer extends AbstractColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return $value;
    }

    #[\Override]
    public function getClasses(): string
    {
        return 'gridView__column--text';
    }
}

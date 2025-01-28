<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\util\StringUtil;

/**
 * Formats the content of a column as a number using `StringUtil::formatNumeric()`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class NumberColumnRenderer extends AbstractColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return StringUtil::formatNumeric($value);
    }

    #[\Override]
    public function getClasses(): string
    {
        return 'gridView__column--digits';
    }
}

<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;

/**
 * Formats the content of a column as an email address.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class EmailColumnRenderer extends AbstractColumnRenderer implements ILinkColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return \sprintf('<a href="mailto:%s">%s</a>', $value, $value);
    }
}

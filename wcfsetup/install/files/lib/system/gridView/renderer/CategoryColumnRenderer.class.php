<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\system\category\CategoryHandler;
use wcf\util\StringUtil;

/**
 * Formats the content of a column as a category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CategoryColumnRenderer extends DefaultColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        if (empty($value)) {
            return '';
        }

        $category = CategoryHandler::getInstance()->getCategory($value);
        if ($category === null) {
            return '';
        }

        return StringUtil::encodeHTML($category->getTitle());
    }
}

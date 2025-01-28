<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Formats the content of a column as a phrase.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class PhraseColumnRenderer extends DefaultColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        return WCF::getLanguage()->get($value);
    }
}

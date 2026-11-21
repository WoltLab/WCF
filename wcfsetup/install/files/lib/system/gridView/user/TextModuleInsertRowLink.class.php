<?php

namespace wcf\system\gridView\user;

use wcf\data\DatabaseObject;
use wcf\system\gridView\AbstractGridViewRowLink;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Turns the rows of the text module grid view into buttons that insert the
 * text module into the editor. The click is handled by the editor's text
 * module tab, which knows the target editor.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TextModuleInsertRowLink extends AbstractGridViewRowLink
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row, bool $isPrimaryColumn = false): string
    {
        $objectID = $row->getObjectID();
        $tabindex = $isPrimaryColumn ? '0' : '-1';
        $title = StringUtil::encodeHTML(WCF::getLanguage()->get('wcf.global.button.insert'));

        return <<<HTML
            <button
                type="button"
                class="gridView__rowLink"
                data-text-module-insert="{$objectID}"
                title="{$title}"
                tabindex="{$tabindex}"
            >
                {$value}
            </button>
            HTML;
    }
}

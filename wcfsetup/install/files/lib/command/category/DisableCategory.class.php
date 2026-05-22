<?php

namespace wcf\command\category;

use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\event\category\CategoryDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DisableCategory
{
    public function __construct(private readonly Category $category) {}

    public function __invoke(): void
    {
        (new CategoryEditor($this->category))->update([
            'isDisabled' => 1,
        ]);

        CategoryEditor::resetCache();

        EventHandler::getInstance()->fire(
            new CategoryDisabled($this->category)
        );
    }
}

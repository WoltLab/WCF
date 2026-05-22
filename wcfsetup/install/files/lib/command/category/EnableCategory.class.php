<?php

namespace wcf\command\category;

use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\event\category\CategoryEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class EnableCategory
{
    public function __construct(private readonly Category $category) {}

    public function __invoke(): void
    {
        (new CategoryEditor($this->category))->update([
            'isDisabled' => 0,
        ]);

        CategoryEditor::resetCache();

        EventHandler::getInstance()->fire(
            new CategoryEnabled($this->category)
        );
    }
}

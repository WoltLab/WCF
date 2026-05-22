<?php

namespace wcf\event\category;

use wcf\data\category\Category;
use wcf\event\IPsr14Event;

/**
 * Indicates that a category has been enabled.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CategoryEnabled implements IPsr14Event
{
    public function __construct(public readonly Category $category) {}
}

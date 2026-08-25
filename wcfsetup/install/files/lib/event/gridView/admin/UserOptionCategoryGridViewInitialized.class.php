<?php

namespace wcf\event\gridView\admin;

use wcf\event\IPsr14Event;
use wcf\system\gridView\admin\UserOptionCategoryGridView;

/**
 * Indicates that the user option category grid view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionCategoryGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly UserOptionCategoryGridView $gridView) {}
}

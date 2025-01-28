<?php

namespace wcf\event\gridView\admin;

use wcf\event\IPsr14Event;
use wcf\system\gridView\admin\ExceptionLogGridView;

/**
 * Indicates that the exception log grid view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ExceptionLogGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly ExceptionLogGridView $gridView) {}
}

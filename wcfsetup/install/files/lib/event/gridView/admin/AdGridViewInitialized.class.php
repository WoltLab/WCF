<?php

namespace wcf\event\gridView\admin;

use wcf\event\IPsr14Event;
use wcf\system\gridView\admin\AdGridView;

/**
 * Indicates that the ad grid view has been initialized.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class AdGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly AdGridView $gridView) {}
}

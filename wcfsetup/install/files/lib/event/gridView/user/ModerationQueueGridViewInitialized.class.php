<?php

namespace wcf\event\gridView\user;

use wcf\event\IPsr14Event;
use wcf\system\gridView\user\ModerationQueueGridView;

/**
 * Indicates that the moderation queue grid view has been initialized.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ModerationQueueGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly ModerationQueueGridView $gridView) {}
}

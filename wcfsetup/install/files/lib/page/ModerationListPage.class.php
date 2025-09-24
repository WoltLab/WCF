<?php

namespace wcf\page;

use wcf\system\gridView\user\ModerationQueueGridView;

/**
 * List of moderation queue entries.
 *
 * @author      Olaf Braun, Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<ModerationQueueGridView>
 */
final class ModerationListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.general.canUseModeration'];

    #[\Override]
    protected function createGridView(): ModerationQueueGridView
    {
        return new ModerationQueueGridView();
    }
}

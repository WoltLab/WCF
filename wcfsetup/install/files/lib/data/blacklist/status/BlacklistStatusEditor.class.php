<?php

namespace wcf\data\blacklist\status;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit blacklist status.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `BlacklistStatusBuilder` instead.
 *
 * @mixin BlacklistStatus
 * @extends DatabaseObjectEditor<BlacklistStatus>
 * @since 5.2
 */
class BlacklistStatusEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BlacklistStatus::class;
}

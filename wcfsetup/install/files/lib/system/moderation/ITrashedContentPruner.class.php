<?php

namespace wcf\system\moderation;

/**
 * Permanently deletes trashed content once it has been in the trash for long enough.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
interface ITrashedContentPruner
{
    /**
     * Permanently deletes up to `$limit` objects that were moved to the trash before `$deletedBefore`.
     */
    public function prune(int $deletedBefore, int $limit): void;
}

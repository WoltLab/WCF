<?php

namespace wcf\data\modification\log;

/**
 * Optional interface for viewable modification logs that can provide the title
 * of the affected object after the object has been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
interface IDeletedObjectAwareModificationLog extends IViewableModificationLog
{
    /**
     * Returns the title of the affected object if the object no longer exists
     * or `null` if the title is unknown.
     */
    public function getDeletedObjectTitle(): ?string;
}

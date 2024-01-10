<?php

namespace wcf\system\acp\dashboard\box;

/**
 * Interface of acp dashboard boxes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
interface IAcpDashboardBox
{
    /**
     * Returns true if the active user has access to this box.
     */
    public function isAccessible(): bool;

    /**
     * Returns the title of this box.
     */
    public function getTitle(): string;

    /**
     * Returns true if this box is not empty.
     */
    public function hasContent(): bool;

    /**
     * Returns the content of this box.
     */
    public function getContent(): string;

    /**
     * Returns the name (identifier) of this box.
     */
    public function getName(): string;
}

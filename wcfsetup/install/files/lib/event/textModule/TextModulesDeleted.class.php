<?php

namespace wcf\event\textModule;

use wcf\data\text\module\TextModule;
use wcf\event\IPsr14Event;

/**
 * Indicates that text modules have been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TextModulesDeleted implements IPsr14Event
{
    /**
     * @param TextModule[] $textModules
     */
    public function __construct(
        public readonly array $textModules,
    ) {}
}

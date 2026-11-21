<?php

namespace wcf\event\textModule;

use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a text module has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TextModuleUpdated implements IPsr14Event
{
    public function __construct(
        public readonly TextModule $textModule,
        public readonly TextModuleBuilder $builder,
    ) {}
}

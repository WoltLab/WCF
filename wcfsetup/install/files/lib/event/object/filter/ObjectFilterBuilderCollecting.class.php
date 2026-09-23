<?php

namespace wcf\event\object\filter;

use wcf\event\IPsr14Event;
use wcf\system\object\filter\builder\IObjectFilterBuilder;

/**
 * Requests the collection of the available object filter builders.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ObjectFilterBuilderCollecting implements IPsr14Event
{
    /**
     * @var array<string, IObjectFilterBuilder>
     */
    private array $builders = [];

    /**
     * Registers a new object filter builder.
     */
    public function register(IObjectFilterBuilder $builder): void
    {
        $this->builders[$builder->getIdentifier()] = $builder;
    }

    /**
     * @return array<string, IObjectFilterBuilder>
     */
    public function getBuilders(): array
    {
        return $this->builders;
    }
}

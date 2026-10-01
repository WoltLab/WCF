<?php

namespace wcf\command\label\group;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupBuilder;
use wcf\event\label\group\LabelGroupUpdated;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a label group.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateLabelGroup
{
    public function __construct(
        private readonly LabelGroupBuilder $builder,
    ) {}

    public function __invoke(): LabelGroup
    {
        $group = $this->builder->update();

        LabelCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new LabelGroupUpdated(
            $group,
            $this->builder
        ));

        return $group;
    }
}

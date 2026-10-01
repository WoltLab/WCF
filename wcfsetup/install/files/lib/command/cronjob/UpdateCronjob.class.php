<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobBuilder;
use wcf\event\cronjob\CronjobUpdated;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a cronjob.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateCronjob
{
    public function __construct(
        private readonly CronjobBuilder $builder,
    ) {}

    public function __invoke(): Cronjob
    {
        $cronjob = $this->builder->update();

        CronjobCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new CronjobUpdated(
            $cronjob,
            $this->builder
        ));

        return $cronjob;
    }
}

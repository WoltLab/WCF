<?php

namespace wcf\command\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobBuilder;
use wcf\event\cronjob\CronjobCreated;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Creates a cronjob.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateCronjob
{
    public function __construct(
        private readonly CronjobBuilder $builder,
    ) {}

    public function __invoke(): Cronjob
    {
        // `CronjobBuilder` may rename the cronjob after its creation, the
        // returned object would carry the placeholder name.
        $cronjob = new Cronjob($this->builder->create()->cronjobID);

        CronjobCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new CronjobCreated(
            $cronjob,
            $this->builder
        ));

        return $cronjob;
    }
}

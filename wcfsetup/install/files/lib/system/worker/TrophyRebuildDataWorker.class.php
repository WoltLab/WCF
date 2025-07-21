<?php

namespace wcf\system\worker;

use wcf\data\trophy\TrophyList;
use wcf\system\trophy\command\MigrateLegacyCondition;

/**
 * Worker implementation for updating trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractLinearRebuildDataWorker<TrophyList>
 */
final class TrophyRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = TrophyList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    #[\Override]
    public function execute()
    {
        parent::execute();

        foreach ($this->objectList as $trophy) {
            (new MigrateLegacyCondition($trophy))();
        }
    }
}

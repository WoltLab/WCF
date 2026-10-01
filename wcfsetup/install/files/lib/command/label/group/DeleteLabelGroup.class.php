<?php

namespace wcf\command\label\group;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\label\group\LabelGroupDeleted;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\label\object\type\ILabelObjectTypeHandler;
use wcf\system\WCF;

/**
 * Deletes a label group and its labels.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteLabelGroup
{
    public function __construct(
        private readonly LabelGroup $group,
    ) {}

    public function __invoke(): void
    {
        // The builder removes the labels and the ACL values outside of its own
        // transaction, which must not persist if the deletion fails.
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            LabelGroupBuilder::delete($this->group);

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType') as $objectType) {
            $handler = $objectType->getProcessor();
            \assert($handler instanceof ILabelObjectTypeHandler);
            $handler->save();
        }

        LabelCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new LabelGroupDeleted($this->group));
    }
}

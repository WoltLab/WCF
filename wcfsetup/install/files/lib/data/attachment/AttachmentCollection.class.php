<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectCollection;
use wcf\data\ITitledLinkObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\TCollectionFiles;
use wcf\system\attachment\IAttachmentObjectType;

/**
 * Represents a collection of attachments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Attachment>
 */
class AttachmentCollection extends DatabaseObjectCollection
{
    use TCollectionFiles;

    private bool $containerObjectsCached = false;

    public function getContainerObject(Attachment $object): ?ITitledLinkObject
    {
        if ($object->objectID === null) {
            return null;
        }

        $this->cacheContainerObjects();

        $objectType = ObjectTypeCache::getInstance()->getObjectType($object->objectTypeID);
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IAttachmentObjectType);

        $containerObject = $processor->getObject($object->objectID);
        // Not every attachment object type returns a container object that
        // implements `ITitledLinkObject`, therefore there is no reliable way to
        // determine a title and a link for those objects.
        if ($containerObject instanceof ITitledLinkObject) {
            return $containerObject;
        }

        return null;
    }

    private function cacheContainerObjects(): void
    {
        if ($this->containerObjectsCached) {
            return;
        }

        $this->containerObjectsCached = true;

        $groupedObjectIDs = [];
        foreach ($this->getObjects() as $attachment) {
            if ($attachment->objectID === null) {
                continue;
            }

            if (!isset($groupedObjectIDs[$attachment->objectTypeID])) {
                $groupedObjectIDs[$attachment->objectTypeID] = [];
            }
            $groupedObjectIDs[$attachment->objectTypeID][] = $attachment->objectID;
        }

        foreach ($groupedObjectIDs as $objectTypeID => $objectIDs) {
            $processor = ObjectTypeCache::getInstance()->getObjectType($objectTypeID)->getProcessor();
            \assert($processor instanceof IAttachmentObjectType);
            $processor->cacheObjects($objectIDs);
        }
    }
}

<?php

namespace wcf\data;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\GroupedAttachmentList;

/**
 * Trait for dbo collections with attachments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionAttachments
{
    private GroupedAttachmentList $attachments;

    /**
     * @return Attachment[]
     */
    public function getAttachments(DatabaseObject $object): array
    {
        if (!$this->hasAttachments($object)) {
            return [];
        }

        $this->loadAttachments();

        return $this->attachments->getGroupedObjects($object->getObjectID());
    }

    private function loadAttachments(): void
    {
        if (isset($this->attachments)) {
            return;
        }

        $this->attachments = new GroupedAttachmentList($this->getAttachmentObjectType());

        $objectIDs = $this->getAttachmentObjectIDs();
        if ($objectIDs === []) {
            return;
        }

        $this->attachments->getConditionBuilder()->add('attachment.objectID IN (?)', [$objectIDs]);
        $this->attachments->readObjects();
        $this->attachments->setPermissions($this->getAttachmentPermissions());
    }

    protected abstract function getAttachmentObjectType(): string;

    /**
     * @return array<string, bool>
     */
    protected function getAttachmentPermissions(): array
    {
        return [
            'canDownload' => true,
            'canViewPreview' => true,
        ];
    }

    protected function hasAttachments(DatabaseObject $object): bool
    {
        return $object->attachments > 0;
    }

    /**
     * @return list<int>
     */
    private function getAttachmentObjectIDs(): array
    {
        \assert($this instanceof DatabaseObjectCollection);

        return \array_map(
            static fn(DatabaseObject $object) => $object->getObjectID(),
            \array_filter($this->getObjects(), fn(DatabaseObject $object) => $this->hasAttachments($object))
        );
    }
}

<?php

namespace wcf\system\attachment;

use wcf\system\cache\runtime\AbstractRuntimeCache;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides a default implementation for attachment object types.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @template T of object
 * @implements IAttachmentObjectType<T>
 */
abstract class AbstractAttachmentObjectType implements IAttachmentObjectType
{
    /**
     * @var array<int, ?T>
     * @deprecated 6.3
     */
    protected array $cachedObjects = [];

    #[\Override]
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.attachment.maxSize');
    }

    #[\Override]
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode("\n", WCF::getSession()->getPermission('user.attachment.allowedExtensions')));
    }

    #[\Override]
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.attachment.maxCount');
    }

    #[\Override]
    public function canViewPreview(int $objectID)
    {
        return $this->canDownload($objectID);
    }

    #[\Override]
    public function getObject(int $objectID)
    {
        if ($this->getObjectRuntimeCache() !== null) {
            return $this->getObjectRuntimeCache()->getObject($objectID);
        }

        return $this->cachedObjects[$objectID] ?? null;
    }

    /**
     * @param array<int, ?T> $objects
     * @return void
     * @deprecated 6.3
     */
    public function setCachedObjects(array $objects)
    {
        foreach ($objects as $id => $object) {
            $this->cachedObjects[$id] = $object;
        }
    }

    /**
     * @return ?AbstractRuntimeCache<*, *>
     */
    protected function getObjectRuntimeCache(): ?AbstractRuntimeCache
    {
        return null;
    }

    #[\Override]
    public function cacheObjects(array $objectIDs)
    {
        if ($this->getObjectRuntimeCache() !== null) {
            $this->getObjectRuntimeCache()->cacheObjectIDs($objectIDs);
        }
    }

    #[\Override]
    public function setPermissions(array $attachments)
    {
        foreach ($attachments as $attachment) {
            $attachment->setPermissions([
                'canDownload' => $this->canDownload($attachment->objectID),
                'canViewPreview' => $this->canViewPreview($attachment->objectID),
            ]);
        }
    }
}

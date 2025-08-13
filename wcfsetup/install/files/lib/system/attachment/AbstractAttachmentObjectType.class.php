<?php

namespace wcf\system\attachment;

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
     * cached objects
     * @var array<int, ?T>
     */
    protected $cachedObjects = [];

    /**
     * @inheritDoc
     */
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.attachment.maxSize');
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode("\n", WCF::getSession()->getPermission('user.attachment.allowedExtensions')));
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.attachment.maxCount');
    }

    /**
     * @inheritDoc
     */
    public function canViewPreview($objectID)
    {
        return $this->canDownload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function getObject($objectID)
    {
        return $this->cachedObjects[$objectID] ?? null;
    }

    /**
     * @param array<int, ?T> $objects
     * @return void
     */
    public function setCachedObjects(array $objects)
    {
        foreach ($objects as $id => $object) {
            $this->cachedObjects[$id] = $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs) {}

    /**
     * @inheritDoc
     */
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

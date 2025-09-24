<?php

namespace wcf\system\attachment;

use wcf\data\attachment\Attachment;

/**
 * Any attachment object type should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @template T of object
 */
interface IAttachmentObjectType
{
    /**
     * Returns true if the active user has the permission to download attachments.
     *
     * @param int $objectID
     * @return bool
     */
    public function canDownload($objectID);

    /**
     * Returns true if the active user has the permission to view attachment
     * previews (thumbnails).
     *
     * @param int $objectID
     * @return bool
     */
    public function canViewPreview($objectID);

    /**
     * Returns true if the active user has the permission to upload attachments.
     *
     * @param int $objectID
     * @param int $parentObjectID
     * @return bool
     */
    public function canUpload($objectID, $parentObjectID = 0);

    /**
     * Returns true if the active user has the permission to delete attachments.
     *
     * @param int $objectID
     * @return bool
     */
    public function canDelete($objectID);

    /**
     * Returns the maximum filesize for an attachment.
     *
     * @return int
     */
    public function getMaxSize();

    /**
     * Returns the allowed file extensions.
     *
     * @return string[]
     */
    public function getAllowedExtensions();

    /**
     * Returns the maximum number of attachments.
     *
     * @return int
     */
    public function getMaxCount();

    /**
     * Returns the container object of an attachment or `null` if the container object does not exist.
     *
     * @param int $objectID
     * @return ?T
     */
    public function getObject($objectID);

    /**
     * Caches the data of the given container objects.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function cacheObjects(array $objectIDs);

    /**
     * Loads the permissions for given attachments.
     *
     * @param Attachment[] $attachments
     * @return void
     */
    public function setPermissions(array $attachments);
}

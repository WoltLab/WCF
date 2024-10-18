<?php

namespace wcf\system\attachment;

use wcf\data\attachment\AttachmentAction;
use wcf\data\attachment\AttachmentList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\file\processor\AttachmentFileProcessor;
use wcf\system\file\processor\FileProcessor;
use wcf\system\WCF;

/**
 * Handles uploaded attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AttachmentHandler implements \Countable
{
    /**
     * object type
     * @var ObjectType
     */
    protected $objectType;

    /**
     * object type
     * @var IAttachmentObjectType
     */
    protected $processor;

    /**
     * object id
     * @var int
     */
    protected $objectID = 0;

    /**
     * parent object id
     * @var int
     */
    protected $parentObjectID = 0;

    /**
     * list of temp hashes
     * @var string[]
     */
    protected $tmpHash = [];

    /**
     * list of attachments
     * @var AttachmentList
     */
    protected $attachmentList;

    private AttachmentFileProcessor $fileProcessor;

    /**
     * Creates a new AttachmentHandler object.
     *
     * @param string $objectType
     * @param int $objectID
     * @param string $tmpHash
     * @param int $parentObjectID
     * @throws  SystemException
     */
    public function __construct($objectType, $objectID, $tmpHash = '', $parentObjectID = 0)
    {
        if (!$objectID && !$tmpHash) {
            throw new SystemException('objectID and tmpHash cannot be empty at the same time');
        }

        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.attachment.objectType',
            $objectType
        );
        $this->processor = $this->objectType->getProcessor();
        $this->objectID = $objectID;
        $this->parentObjectID = $parentObjectID;

        if (\strpos($tmpHash, ',') !== false) {
            $this->tmpHash = \explode(',', $tmpHash);
        } else {
            $this->tmpHash = [$tmpHash];
        }
    }

    /**
     * Returns a list of attachments.
     *
     * @return  AttachmentList
     */
    public function getAttachmentList()
    {
        if ($this->attachmentList === null) {
            $this->attachmentList = new AttachmentList();
            $this->attachmentList->sqlOrderBy = 'attachment.showOrder';
            $this->attachmentList->getConditionBuilder()->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
            if ($this->objectID) {
                $this->attachmentList->getConditionBuilder()->add('objectID = ?', [$this->objectID]);
            } else {
                $this->attachmentList->getConditionBuilder()->add('tmpHash IN (?)', [$this->tmpHash]);
            }
            $this->attachmentList->readObjects();
        }

        return $this->attachmentList;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->getAttachmentList());
    }

    /**
     * Sets the object id of temporary saved attachments.
     *
     * @param int $objectID
     */
    public function updateObjectID($objectID)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$this->objectType->objectTypeID]);
        $conditions->add("tmpHash IN (?)", [$this->tmpHash]);
        $conditions->add("(objectID IS NULL OR objectID = 0)");

        $sql = "UPDATE  wcf1_attachment
                SET     objectID = ?,
                        tmpHash = ''
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);

        $parameters = $conditions->getParameters();
        \array_unshift($parameters, $objectID);

        $statement->execute($parameters);
    }

    /**
     * Transfers attachments to a different object id of the same type (e.g. merging content)
     *
     * @param string $objectType
     * @param int $newObjectID
     * @param int[] $oldObjectIDs
     */
    public static function transferAttachments($objectType, $newObjectID, array $oldObjectIDs)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [
            ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.attachment.objectType',
                $objectType
            )->objectTypeID,
        ]);
        $conditions->add("objectID IN (?)", [$oldObjectIDs]);
        $parameters = $conditions->getParameters();
        \array_unshift($parameters, $newObjectID);

        $sql = "UPDATE  wcf1_attachment
                SET     objectID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($parameters);
    }

    /**
     * Removes all attachments for given object ids by type.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     */
    public static function removeAttachments($objectType, array $objectIDs)
    {
        $attachmentList = new AttachmentList();
        $attachmentList->getConditionBuilder()->add("objectTypeID = ?", [
            ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.attachment.objectType',
                $objectType
            )->objectTypeID,
        ]);
        $attachmentList->getConditionBuilder()->add("objectID IN (?)", [$objectIDs]);
        $attachmentList->readObjects();

        if (\count($attachmentList)) {
            $attachmentAction = new AttachmentAction($attachmentList->getObjects(), 'delete');
            $attachmentAction->executeAction();
        }
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize()
    {
        return $this->processor->getMaxSize();
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return $this->processor->getAllowedExtensions();
    }

    /**
     * Returns a formatted list of the allowed file extensions.
     *
     * @return  string[]
     */
    public function getFormattedAllowedExtensions()
    {
        $extensions = $this->getAllowedExtensions();

        // sort
        \sort($extensions);

        // check wildcards
        for ($i = 0, $j = \count($extensions); $i < $j; $i++) {
            if (\strpos($extensions[$i], '*') !== false) {
                for ($k = $j - 1; $k > $i; $k--) {
                    if (
                        \preg_match(
                            '/^' . \str_replace('\*', '.*', \preg_quote($extensions[$i], '/')) . '$/i',
                            $extensions[$k]
                        )
                    ) {
                        \array_splice($extensions, $k, 1);
                        $j--;
                    }
                }
            }
        }

        return $extensions;
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount()
    {
        return $this->processor->getMaxCount();
    }

    /**
     * Returns true if the active user has the permission to upload attachments.
     *
     * @return  bool
     */
    public function canUpload()
    {
        return $this->processor->canUpload($this->objectID, $this->parentObjectID);
    }

    /**
     * Returns the object type processor.
     *
     * @return  IAttachmentObjectType
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Returns the temporary hashes used to identify the relevant uploaded attachments.
     *
     * @return  string[]
     * @since   5.2
     */
    public function getTmpHashes()
    {
        return $this->tmpHash;
    }

    /**
     * Sets the temporary hashes used to identify the relevant uploaded attachments.
     *
     * @param string[] $tmpHash
     * @since   5.2
     */
    public function setTmpHashes(array $tmpHash)
    {
        $this->tmpHash = $tmpHash;
    }

    /**
     * Returns the attachment object type
     *
     * @return  ObjectType
     * @since   5.2
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * Returns the id of the object the handled attachments belong to. If the object does not
     * exist (yet), `0` is returned.
     *
     * @return  int
     * @since   5.2
     */
    public function getObjectID()
    {
        return $this->objectID;
    }

    /**
     * Returns the id of the parent object of the object the handled attachments belong to.
     * If no such parent object exists, `0` is returned.
     *
     * @return  int
     * @since   5.2
     */
    public function getParentObjectID()
    {
        return $this->parentObjectID;
    }

    public function getHtmlElement(): string
    {
        return $this->getFileProcessor()->toHtmlElement(
            $this->objectType->objectType,
            $this->objectID ?? 0,
            \implode(',', $this->tmpHash),
            $this->parentObjectID
        );
    }

    private function getFileProcessor(): AttachmentFileProcessor
    {
        if (!isset($this->fileProcessor)) {
            $this->fileProcessor = FileProcessor::getInstance()->getProcessorByName('com.woltlab.wcf.attachment');
        }

        return $this->fileProcessor;
    }
}

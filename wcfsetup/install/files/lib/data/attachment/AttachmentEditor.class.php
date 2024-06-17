<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectEditor;
use wcf\data\file\File;
use wcf\data\file\FileEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Attachment  create(array $parameters = [])
 * @method      Attachment  getDecoratedObject()
 * @mixin       Attachment
 */
class AttachmentEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = Attachment::class;

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $sql = "DELETE FROM wcf" . WCF_N . "_attachment
                WHERE       attachmentID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->attachmentID]);

        $this->deleteFiles();
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        // delete files first
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add("attachmentID IN (?)", [$objectIDs]);

        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_attachment
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        while ($attachment = $statement->fetchObject(static::$baseClass)) {
            $editor = new self($attachment);
            $editor->deleteFiles();
        }

        return parent::deleteAll($objectIDs);
    }

    /**
     * Deletes attachment files.
     */
    public function deleteFiles()
    {
        if ($this->fileID !== null) {
            $fileEditor = new FileEditor(new File($this->fileID));
            $fileEditor->delete();
            return;
        }

        @\unlink($this->getLocation());
        if ($this->tinyThumbnailType) {
            @\unlink($this->getTinyThumbnailLocation());
        }
        if ($this->thumbnailType) {
            @\unlink($this->getThumbnailLocation());
        }
    }
}

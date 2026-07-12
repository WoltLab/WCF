<?php

namespace wcf\system\file\upload;

use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;

/**
 * Handles uploads for files.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @phpstan-type StorageEntry array{
 *  field: UploadField,
 *  files: UploadFile[],
 *  removedFiles: UploadFile[],
 *  registered: int,
 * }
 */
class UploadHandler extends SingletonFactory
{
    /**
     * Session variable name for the file storage.
     * @var string
     */
    const UPLOAD_HANDLER_SESSION_VAR = 'file_upload_handler_storage';

    /**
     * Contains the valid image extensions w/o svg.
     * @var string[]
     * @deprecated 5.3 Use \wcf\util\ImageUtil::$imageExtensions instead (direct replacement).
     */
    const VALID_IMAGE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

    /**
     * @since 5.5
     */
    private const UPLOAD_FIELD_SESSION_TIMEOUT = 3600 * 24;

    /**
     * Contains the registered upload fields.
     *
     * @var array<string, UploadField>
     */
    protected $fields = [];

    #[\Override]
    protected function init()
    {
        $this->cleanupStorage();
    }

    /**
     * Registers a UploadField.
     *
     * @param ?mixed[] $requestData
     * @return void
     * @throws \InvalidArgumentException if a field with the given fieldId is already registered
     */
    public function registerUploadField(UploadField $field, ?array $requestData = null)
    {
        if (isset($this->fields[$field->getFieldId()])) {
            throw new \InvalidArgumentException(
                'UploadField with the id "' . $field->getFieldId() . '" is already registered.'
            );
        }

        if ($requestData === null) {
            $requestData = $_POST;
        }

        // read internal identifier
        if (
            !empty($requestData)
            && isset($requestData[$field->getFieldId()])
            && $this->isValidInternalId($requestData[$field->getFieldId()])
        ) {
            $field->setInternalId($requestData[$field->getFieldId()]);

            $this->fields[$field->getFieldId()] = $field;
        } else {
            $internalId = StringUtil::getRandomID();

            $field->setInternalId($internalId);

            $this->registerFieldInStorage($field);
        }
    }

    /**
     * Unregisters an upload field by the given field id.
     *
     * @return void
     *
     * @throws \InvalidArgumentException if the given fieldId is unknown
     */
    public function unregisterUploadField(string $fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('UploadField with the id "' . $fieldId . '" is unknown.');
        }

        $storage = $this->getStorage();
        unset($storage[$this->fields[$fieldId]->getInternalId()]);

        WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);

        unset($this->fields[$fieldId]);
    }

    /**
     * Returns the uploaded files for a specific fieldId.
     *
     * @return UploadFile[]
     *
     * @throws \InvalidArgumentException if the given fieldId is unknown
     */
    public function getFilesByFieldId(string $fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('UploadField with the id "' . $fieldId . '" is unknown.');
        }

        return $this->getFilesByInternalId($this->fields[$fieldId]->getInternalId());
    }

    /**
     * Returns the removed but previosly proccessed files for a specific fieldId.
     *
     * @return UploadFile[]
     *
     * @throws \InvalidArgumentException if the given fieldId is unknown
     */
    public function getRemovedFilesByFieldId(string $fieldId, bool $processFiles = true)
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('UploadField with the id "' . $fieldId . '" is unknown.');
        }

        return $this->getRemovedFilesByInternalId($this->fields[$fieldId]->getInternalId(), $processFiles);
    }

    /**
     * @return UploadFile[]
     * @deprecated 6.0 This method exists only because of a spelling error in the method name. Use `getRemovedFiledByFieldId` instead.
     */
    public function getRemovedFiledByFieldId(string $fieldId, bool $processFiles = true)
    {
        return $this->getRemovedFilesByFieldId($fieldId, $processFiles);
    }

    /**
     * Returns the removed but previosly proccessed files for a specific internalId.
     *
     * @return UploadFile[]
     */
    public function getRemovedFilesByInternalId(string $internalId, bool $processFiles = true)
    {
        if (isset($this->getStorage()[$internalId])) {
            $files = $this->getStorage()[$internalId]['removedFiles'];
            $removedFiles = [];

            /** @var UploadFile $file */
            foreach ($files as $file) {
                if (\file_exists($file->getLocation())) {
                    $removedFiles[] = $file;
                }
            }

            if ($processFiles) {
                $this->processRemovedFiles($this->getFieldByInternalId($internalId));
            }

            return $removedFiles;
        }

        return [];
    }

    /**
     * @return UploadFile[]
     * @deprecated 6.0 This method exists only because of a spelling error in the method name. Use `getRemovedFilesByInternalId` instead.
     */
    public function getRemovedFiledByInternalId(string $internalId, bool $processFiles = true)
    {
        return $this->getRemovedFilesByInternalId($internalId, $processFiles);
    }

    /**
     * Removes a file from the upload.
     *
     * @return void
     *
     * @throws \InvalidArgumentException if the given internalId is unknown
     */
    public function removeFile(string $internalId, string $uniqueFileId)
    {
        if (!$this->isValidInternalId($internalId)) {
            throw new \InvalidArgumentException('InternalId "' . $internalId . '" is unknown.');
        }

        $file = $this->getFileByUniqueFileId($internalId, $uniqueFileId);

        if ($file === null) {
            return;
        }

        $this->removeFileByObject($internalId, $file);
    }

    /**
     * Removes an file by file object.
     *
     * @param UploadFile $file
     * @return void
     */
    private function removeFileByObject(string $internalId, UploadFile $file)
    {
        $storage = $this->getStorage();

        if ($file->isProcessed()) {
            $storage[$internalId]['removedFiles'] = \array_merge($storage[$internalId]['removedFiles'], [$file]);
        } else {
            @\unlink($file->getLocation());
        }

        /** @var UploadFile $storageFile */
        foreach ($storage[$internalId]['files'] as $id => $storageFile) {
            if ($storageFile->getUniqueFileId() === $file->getUniqueFileId()) {
                unset($storage[$internalId]['files'][$id]);
                break;
            }
        }

        WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
    }

    /**
     * Renders the field with the given fieldId for the template.
     *
     * @return string
     *
     * @throws \InvalidArgumentException if the given fieldId is unknown
     */
    public function renderField(string $fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('UploadField with the id "' . $fieldId . '" is unknown.');
        }

        return WCF::getTPL()->render('wcf', 'shared_uploadFieldComponent', [
            'uploadField' => $this->fields[$fieldId],
            'uploadFieldId' => $fieldId,
            'uploadFieldFiles' => $this->getFilesByFieldId($fieldId),
        ]);
    }

    /**
     * Returns true, if the given internalId is valid.
     *
     * @return bool
     */
    public function isValidInternalId(string $internalId)
    {
        return isset($this->getStorage()[$internalId]);
    }

    /**
     * Checks whether the passed internal file id is valid for an internal id.
     *
     * @return bool
     */
    public function isValidUniqueFileId(string $internalId, string $uniqueFileId)
    {
        return $this->getFileByUniqueFileId($internalId, $uniqueFileId) !== null;
    }

    /**
     * Return all files by file id.
     *
     * @return ?UploadFile
     *
     * @throws \InvalidArgumentException if the given internalId is unknown
     */
    public function getFileByUniqueFileId(string $internalId, string $uniqueFileId)
    {
        if (!$this->isValidInternalId($internalId)) {
            throw new \InvalidArgumentException('InternalId "' . $internalId . '" is unknown.');
        }

        foreach ($this->getFilesByInternalId($internalId) as $file) {
            if (\hash_equals($file->getUniqueFileId(), $uniqueFileId)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Add a file for an internalId.
     *
     * @return void
     */
    public function addFileByInternalId(string $internalId, UploadFile $file)
    {
        $this->registerFilesByInternalId($internalId, \array_merge($this->getFilesByInternalId($internalId), [$file]));
    }

    /**
     * Registers files for the given internalId.
     *
     * <strong>Attention</strong>: Deletes all uploaded files and overwrites them with
     * the given files. If you want to add a file, use the addFileForInternalId method.
     *
     * @param UploadFile[] $files
     * @return void
     *
     * @throws \InvalidArgumentException       if the given internalId is unknown
     */
    public function registerFilesByInternalId(string $internalId, array $files)
    {
        if (!$this->isValidInternalId($internalId)) {
            throw new \InvalidArgumentException('InternalId "' . $internalId . '" is unknown.');
        }

        foreach ($files as $file) {
            if (!($file instanceof UploadFile)) {
                throw new ImplementationException(\get_class($file), UploadFile::class);
            }
        }

        $storage = $this->getStorage();
        $storage[$internalId]['files'] = $files;

        WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
    }

    /**
     * Add a file for an upload field with the given fieldId.
     *
     * @param UploadFile $file
     * @return void
     */
    public function addFileByField(string $fieldId, UploadFile $file)
    {
        $this->registerFilesByField($fieldId, \array_merge($this->getFilesByFieldId($fieldId), [$file]));
    }

    /**
     * Register files for the field with the given fieldId.
     *
     * <strong>Attention</strong>: Deletes all uploaded files and overwrites them with
     * the given files. If you want to add a file, use the addFileForField method.
     *
     * @param UploadFile[] $files
     * @return void
     *
     * @throws \InvalidArgumentException if the given fieldId is unknown
     */
    public function registerFilesByField(string $fieldId, array $files)
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('UploadField with the id "' . $fieldId . '" is unknown.');
        }

        $this->registerFilesByInternalId($this->fields[$fieldId]->getInternalId(), $files);
    }

    /**
     * Returns the field for the internalId.
     *
     * @return UploadField
     *
     * @throws \InvalidArgumentException       if the given internalId is unknown
     */
    public function getFieldByInternalId(string $internalId)
    {
        if (!$this->isValidInternalId($internalId)) {
            throw new \InvalidArgumentException('InternalId "' . $internalId . '" is unknown.');
        }

        return $this->getStorage()[$internalId]['field'];
    }

    /**
     * Returns the count of uploaded files for an internal id.
     *
     * @return int
     */
    public function getFilesCountByInternalId(string $internalId)
    {
        return \count($this->getFilesByInternalId($internalId));
    }

    /**
     * Returns true, iff a field with the given fieldId is already registered.
     *
     * @return bool
     */
    public function isRegisteredFieldId(string $fieldId)
    {
        return isset($this->fields[$fieldId]);
    }

    /**
     * Returns the files for an internal identifier.
     *
     * @return UploadFile[]
     */
    private function getFilesByInternalId(string $internalId): array
    {
        if (isset($this->getStorage()[$internalId])) {
            $files = $this->getStorage()[$internalId]['files'];

            // check availability of the files
            /** @var UploadFile $file */
            foreach ($files as $file) {
                if (!\file_exists($file->getLocation())) {
                    $this->removeFileByObject($internalId, $file);
                }
            }

            return $files;
        }

        return [];
    }

    /**
     * Returns the upload handler storage, located in the session var.
     *
     * @return array<string, StorageEntry>
     */
    private function getStorage(): array
    {
        if (!\is_array(WCF::getSession()->getVar(self::UPLOAD_HANDLER_SESSION_VAR))) {
            return [];
        }

        return WCF::getSession()->getVar(self::UPLOAD_HANDLER_SESSION_VAR);
    }

    /**
     * @since 5.5
     */
    private function cleanupStorage(): void
    {
        $hasChanges = false;
        $storage = $this->getStorage();

        foreach ($storage as $internalID => $storageData) {
            if ($storageData['registered'] < (\TIME_NOW - self::UPLOAD_FIELD_SESSION_TIMEOUT)) {
                unset($storage[$internalID]);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
        }
    }

    /**
     * Registers an field in the storage.
     *
     * @param UploadField $field
     */
    private function registerFieldInStorage(UploadField $field): void
    {
        $storage = $this->getStorage();
        $storage[$field->getInternalId()] = [
            'field' => $field,
            'files' => [],
            'removedFiles' => [],
            'registered' => \TIME_NOW,
        ];

        $this->fields[$field->getFieldId()] = $field;

        WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
    }

    /**
     * Remove the removedFiles from the upload process.
     */
    private function processRemovedFiles(UploadField $field): void
    {
        $storage = $this->getStorage();
        $storage[$field->getInternalId()]['removedFiles'] = [];

        WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
    }

    /**
     * Returns true, iff the given location contains an image.
     *
     * @return      bool
     * @deprecated  5.3 Use \wcf\util\ImageUtil::isImage() instead (direct replacement).
     */
    public static function isValidImage(string $location, string $imageName, bool $svgImageAllowed)
    {
        return ImageUtil::isImage($location, $imageName, $svgImageAllowed);
    }
}

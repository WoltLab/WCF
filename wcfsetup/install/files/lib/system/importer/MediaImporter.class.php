<?php

namespace wcf\system\importer;

use wcf\data\media\Media;
use wcf\data\media\MediaAction;
use wcf\data\media\MediaEditor;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\upload\DefaultUploadFileSaveStrategy;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Imports cms media.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Importer
 */
class MediaImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Media::class;

    /**
     * @var DefaultUploadFileSaveStrategy
     */
    private $saveStrategy;

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        // check file location
        if (!\is_readable($additionalData['fileLocation'])) {
            return 0;
        }

        // Extract metadata from the file ourselves, because the
        // information pulled from the source database might not
        // be reliable.
        $data['fileHash'] = \sha1_file($additionalData['fileLocation']);
        $data['filesize'] = \filesize($additionalData['fileLocation']);
        $data['fileType'] = FileUtil::getMimeType($additionalData['fileLocation']);

        $imageData = @\getimagesize($additionalData['fileLocation']);
        if ($imageData !== false) {
            $data['isImage'] = 1;
            $data['width'] = $imageData[0];
            $data['height'] = $imageData[1];
        } else {
            $data['isImage'] = 0;
            $data['width'] = 0;
            $data['height'] = 0;
        }

        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);

        $contents = [];
        if (!empty($additionalData['contents'])) {
            foreach ($additionalData['contents'] as $languageCode => $contentData) {
                $languageID = 0;
                if (!$languageCode) {
                    if (($language = LanguageFactory::getInstance()->getLanguageByCode($languageCode)) !== null) {
                        $languageID = $language->languageID;
                    } else {
                        continue;
                    }
                }

                $contents[$languageID] = [
                    'title' => (!empty($contentData['title']) ? $contentData['title'] : ''),
                    'caption' => (!empty($contentData['caption']) ? $contentData['caption'] : ''),
                    'altText' => (!empty($contentData['altText']) ? $contentData['altText'] : ''),
                ];
            }
            if (\count($contents) > 1) {
                $data['isMultilingual'] = 1;
            }
        }

        // handle language
        if (!empty($additionalData['languageCode'])) {
            if (($language = LanguageFactory::getInstance()->getLanguageByCode($additionalData['languageCode'])) !== null) {
                $data['languageID'] = $language->languageID;
            }
        }

        // check old id
        if (\ctype_digit((string)$oldID)) {
            $media = new Media($oldID);
            if (!$media->mediaID) {
                $data['mediaID'] = $oldID;
            }
        }

        // category
        $categoryID = null;
        if (!empty($data['categoryID'])) {
            $categoryID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.media.category', $data['categoryID']);
        }
        if ($categoryID !== null) {
            $data['categoryID'] = $categoryID;
        }

        // save media
        $media = MediaEditor::create($data);

        // check media directory
        // and create subdirectory if necessary
        $dir = \dirname($media->getLocation());
        if (!@\file_exists($dir)) {
            @\mkdir($dir, 0777);
        }

        // copy file
        try {
            if (!\copy($additionalData['fileLocation'], $media->getLocation())) {
                throw new SystemException();
            }
        } catch (SystemException $e) {
            // copy failed; delete media
            $editor = new MediaEditor($media);
            $editor->delete();

            return 0;
        }

        // save media content
        $sql = "INSERT INTO wcf" . WCF_N . "_media_content
                            (mediaID, languageID, title, caption, altText)
                VALUES      (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        foreach ($contents as $languageID => $contentData) {
            $statement->execute([
                $media->mediaID,
                $languageID ?: null,
                $contentData['title'],
                $contentData['caption'],
                $contentData['altText'],
            ]);
        }

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.media', $oldID, $media->mediaID);

        return $media->mediaID;
    }

    /**
     * @return DefaultUploadFileSaveStrategy
     */
    private function getSaveStrategy()
    {
        if ($this->saveStrategy === null) {
            $this->saveStrategy = new DefaultUploadFileSaveStrategy(MediaAction::class);
        }

        return $this->saveStrategy;
    }
}

<?php

namespace wcf\system\importer;

use wcf\command\media\CreateMedia;
use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\util\FileUtil;

/**
 * Imports cms media.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Media::class;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        // check file location
        if (!\is_readable($additionalData['fileLocation'])) {
            return 0;
        }

        // Extract metadata from the file ourselves, because the
        // information pulled from the source database might not
        // be reliable.
        $imageData = @\getimagesize($additionalData['fileLocation']);

        $builder = MediaBuilder::forCreate()
            ->setFileData(
                $data['filename'],
                \filesize($additionalData['fileLocation']),
                FileUtil::getMimeType($additionalData['fileLocation']),
                $imageData !== false,
                $imageData !== false ? $imageData[0] : 0,
                $imageData !== false ? $imageData[1] : 0,
            )
            ->setFileHash(\sha1_file($additionalData['fileLocation']))
            ->setUser(
                ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID'] ?? null),
                $data['username'] ?? ''
            )
            ->setUploadTime($data['uploadTime'] ?? \TIME_NOW)
            ->setCaptionEnableHtml((bool)($data['captionEnableHtml'] ?? false));

        if (isset($data['fileUpdateTime'])) {
            $builder->setFileUpdateTime((int)$data['fileUpdateTime']);
        }
        if (isset($data['downloads'])) {
            $builder->setCustomProperty('downloads', (int)$data['downloads']);
        }
        if (isset($data['lastDownloadTime'])) {
            $builder->setCustomProperty('lastDownloadTime', (int)$data['lastDownloadTime']);
        }

        $languageCode = (string)($additionalData['languageCode'] ?? '');
        if ($languageCode !== '') {
            $language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
            if ($language !== null) {
                $builder->setCustomProperty('languageID', $language->languageID);
            }
        }

        $this->setContents($builder, $additionalData['contents'] ?? []);

        // check old id
        if (\ctype_digit((string)$oldID)) {
            $media = new Media($oldID);
            if ($media->isNil()) {
                $builder->setID($oldID);
            }
        }

        // category
        if (isset($data['categoryID'])) {
            $builder->setCategoryID(
                ImportHandler::getInstance()->getNewID('com.woltlab.wcf.media.category', $data['categoryID'])
            );
        }

        $media = (new CreateMedia($builder))();

        // check media directory
        // and create subdirectory if necessary
        $dir = \dirname($media->getLocation());
        if (!@\file_exists($dir)) {
            @\mkdir($dir, 0777);
        }

        // copy file
        if (!@\copy($additionalData['fileLocation'], $media->getLocation())) {
            // copy failed; delete media
            MediaBuilder::delete($media);

            return 0;
        }

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.media', $oldID, $media->mediaID);

        return $media->mediaID;
    }

    /**
     * Sets the localized title, caption and alternative text from the
     * imported contents which are grouped by language code.
     *
     * @param array<string, array{title?: string, caption?: string, altText?: string}> $contents
     */
    private function setContents(MediaBuilder $builder, array $contents): void
    {
        $values = [];
        foreach ($contents as $languageCode => $contentData) {
            $languageID = L10nStorage::MONOLINGUAL;
            if ($languageCode !== '') {
                $language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
                if ($language === null) {
                    continue;
                }

                $languageID = $language->languageID;
            }

            $values[$languageID] = $contentData;
        }

        if ($values === []) {
            return;
        }

        // A single content is shown in every language.
        if (\count($values) === 1) {
            $values = [L10nStorage::MONOLINGUAL => \reset($values)];
        } else {
            unset($values[L10nStorage::MONOLINGUAL]);

            $builder->setCustomProperty('isMultilingual', 1);
        }

        foreach (['title', 'caption', 'altText'] as $columnName) {
            $columnValues = \array_map(
                static fn(array $contentData) => $contentData[$columnName] ?? '',
                $values
            );

            match ($columnName) {
                'title' => $builder->setTitle($columnValues),
                'caption' => $builder->setCaption($columnValues),
                'altText' => $builder->setAltText($columnValues),
            };
        }
    }
}

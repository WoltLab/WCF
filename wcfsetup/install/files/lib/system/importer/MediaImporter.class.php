<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\command\media\CreateMedia;
use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;

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

        // The meta data is extracted from the file itself, because the
        // information pulled from the source database might not be reliable.
        $file = new CreateFileFromExistingFile(
            $additionalData['fileLocation'],
            $data['filename'],
            'com.woltlab.wcf.media',
            copy: true,
            uploadTime: isset($data['uploadTime']) ? (int)$data['uploadTime'] : null,
        )();

        // Images whose dimensions cannot be read are considered to be broken
        // and are skipped, matching the behavior of the attachment import.
        if ($file === null) {
            return 0;
        }

        $builder = MediaBuilder::forCreate()
            ->setFile($file)
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

        // Media files without an explicit language belong to the default language.
        $languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        $languageCode = (string)($additionalData['languageCode'] ?? '');
        if ($languageCode !== '') {
            $language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
            if ($language !== null) {
                $languageID = $language->languageID;
            }
        }
        $builder->setLanguageID($languageID);

        $this->setContents($builder, $additionalData['contents'] ?? [], $languageCode === '');

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

        $media = new CreateMedia($builder)();

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.media', $oldID, $media->mediaID);

        return $media->mediaID;
    }

    /**
     * Sets the localized title, caption and alternative text from the
     * imported contents which are grouped by language code. Multiple contents
     * make the media file multilingual, a single content of a specific language
     * assigns the media file to that language unless a language was given.
     * Contents without a language code (`''` or `0`) are monolingual.
     *
     * @param array<int|string, array{title?: string, caption?: string, altText?: string}> $contents
     */
    private function setContents(MediaBuilder $builder, array $contents, bool $adoptContentLanguage): void
    {
        $values = [];
        foreach ($contents as $languageCode => $contentData) {
            $languageCode = (string)$languageCode;
            $languageID = L10nStorage::MONOLINGUAL;
            if ($languageCode !== '' && $languageCode !== '0') {
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

        if (\count($values) === 1) {
            $contentLanguageID = \array_key_first($values);
            if ($adoptContentLanguage && $contentLanguageID !== L10nStorage::MONOLINGUAL) {
                $builder->setLanguageID($contentLanguageID);
            }

            $values = [L10nStorage::MONOLINGUAL => \reset($values)];
        } else {
            unset($values[L10nStorage::MONOLINGUAL]);

            $builder->setMultilingual();
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

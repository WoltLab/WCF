<?php

namespace wcf\data\media;

use wcf\command\media\DeleteMedia;
use wcf\command\media\UpdateMedia;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Executes media file-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `MediaBuilder` and commands instead.
 *
 * @extends AbstractDatabaseObjectAction<Media, MediaEditor>
 */
class MediaAction extends AbstractDatabaseObjectAction
{
    #[\Override]
    public function validateUpdate()
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);

        $this->readObjectsAndCheckOwnership();

        $this->readInteger('categoryID', true, 'data');
        $this->readInteger('captionEnableHtml', true, 'data');
        $this->readInteger('languageID', true, 'data');

        if (
            $this->parameters['data']['languageID'] !== 0
            && LanguageFactory::getInstance()->getLanguage($this->parameters['data']['languageID']) === null
        ) {
            throw new UserInputException('languageID');
        }

        if ($this->parameters['data']['categoryID'] !== 0) {
            $category = CategoryHandler::getInstance()->getCategory($this->parameters['data']['categoryID']);
            if ($category === null || $category->getObjectType()->objectType !== 'com.woltlab.wcf.media.category') {
                throw new UserInputException('categoryID');
            }
        }
    }

    #[\Override]
    public function update()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $data = $this->parameters['data'] ?? [];

        foreach ($this->getObjects() as $mediaEditor) {
            $builder = MediaBuilder::forUpdate($mediaEditor->getDecoratedObject());

            if (\array_key_exists('categoryID', $data)) {
                $builder->setCategoryID($data['categoryID'] ?: null);
            }
            if (isset($data['captionEnableHtml'])) {
                $builder->setCaptionEnableHtml((bool)$data['captionEnableHtml']);
            }
            $languageID = (int)($data['languageID'] ?? 0);
            if ((bool)($data['isMultilingual'] ?? false)) {
                $builder->setMultilingual();
            } elseif (isset($data['isMultilingual']) || $languageID !== 0) {
                $builder->setLanguageID(
                    $languageID ?: $mediaEditor->getDecoratedObject()->getLanguageID()
                        ?? LanguageFactory::getInstance()->getDefaultLanguageID()
                );
            }

            if (\count($this->objects) === 1) {
                if (isset($this->parameters['title'])) {
                    $builder->setTitle($this->getL10nValuesFromParameter($this->parameters['title']));
                }
                if (isset($this->parameters['caption'])) {
                    $builder->setCaption($this->getL10nValuesFromParameter($this->parameters['caption']));
                }
                if (isset($this->parameters['altText'])) {
                    $builder->setAltText($this->getL10nValuesFromParameter($this->parameters['altText']));
                }

                if (!empty($this->parameters['aclValues'])) {
                    $builder->setAclValues([
                        'allowAll' => (bool)($this->parameters['aclValues']['allowAll'] ?? true),
                        'user' => \array_values(\array_map(\intval(...), $this->parameters['aclValues']['user'] ?? [])),
                        'group' => \array_values(\array_map(\intval(...), $this->parameters['aclValues']['group'] ?? [])),
                    ]);
                }
            }

            new UpdateMedia($builder)();
        }
    }

    /**
     * Converts a monolingual value or a `languageID => value` map into l10n values.
     *
     * @param string|array<int, string> $parameter
     * @return array<non-negative-int, string>
     */
    private function getL10nValuesFromParameter(string|array $parameter): array
    {
        if (\is_string($parameter)) {
            return [L10nStorage::MONOLINGUAL => $parameter];
        }

        if (\count($parameter) === 1) {
            return [L10nStorage::MONOLINGUAL => \reset($parameter)];
        }

        $values = [];
        foreach ($parameter as $languageID => $value) {
            if ($languageID > 0) {
                $values[$languageID] = $value;
            }
        }

        return $values ?: [L10nStorage::MONOLINGUAL => ''];
    }

    #[\Override]
    public function validateDelete()
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMedia']);

        $this->readObjectsAndCheckOwnership();
    }

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $media = \array_map(
            static fn(MediaEditor $mediaEditor) => $mediaEditor->getDecoratedObject(),
            $this->getObjects()
        );
        new DeleteMedia($media)();

        return \count($media);
    }

    private function readObjectsAndCheckOwnership(): void
    {
        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $mediaEditor) {
            if (!$mediaEditor->getDecoratedObject()->canManage()) {
                throw new PermissionDeniedException();
            }
        }
    }
}

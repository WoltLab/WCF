<?php

namespace wcf\data\language\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\event\language\PhraseChanged;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Executes language item-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LanguageItem        create()
 * @method  LanguageItemEditor[]    getObjects()
 * @method  LanguageItemEditor  getSingleObject()
 */
class LanguageItemAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = LanguageItemEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.language.canManageLanguage'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.language.canManageLanguage'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.language.canManageLanguage'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'edit', 'prepareEdit', 'update'];

    /**
     * Creates multiple language items.
     *
     * @since   5.2
     */
    public function createLanguageItems()
    {
        if (!isset($this->parameters['data']['packageID'])) {
            $this->parameters['data']['packageID'] = 1;
        }

        if (!empty($this->parameters['languageItemValue_i18n'])) {
            // multiple languages
            foreach ($this->parameters['languageItemValue_i18n'] as $languageID => $value) {
                (new self([], 'create', [
                    'data' => \array_merge(
                        $this->parameters['data'],
                        [
                            'languageID' => $languageID,
                            'languageItemValue' => $value,
                        ]
                    ),
                ]))->executeAction();
            }
        } else {
            // single language
            (new self([], 'create', [
                'data' => \array_merge(
                    $this->parameters['data'],
                    [
                        'languageID' => LanguageFactory::getInstance()->getDefaultLanguageID(),
                    ]
                ),
            ]))->executeAction();
        }
    }

    /**
     * Validates parameters to prepare edit.
     */
    public function validatePrepareEdit()
    {
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            throw new PermissionDeniedException();
        }

        $this->readObjects();
        if (!\count($this->objects)) {
            throw new UserInputException('objectIDs');
        }
    }

    /**
     * Prepares edit.
     */
    public function prepareEdit()
    {
        $item = \reset($this->objects);
        WCF::getTPL()->assign([
            'item' => $item,
        ]);

        return [
            'languageItem' => $item->languageItem,
            'template' => WCF::getTPL()->fetch('languageItemEditDialog'),
        ];
    }

    /**
     * Validates edit action.
     */
    public function validateEdit()
    {
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            throw new PermissionDeniedException();
        }

        $this->readObjects();
        if (!\count($this->objects)) {
            throw new UserInputException('objectIDs');
        }

        $this->readString('languageItemValue', true);
        $this->readString('languageCustomItemValue', true);
        $this->readBoolean('languageUseCustomValue', true);
    }

    /**
     * Edits an item.
     */
    public function edit()
    {
        // save item
        /** @var LanguageItemEditor $editor */
        $editor = \reset($this->objects);
        if ($editor->languageItemOriginIsSystem) {
            $updateData = [
                'languageCustomItemValue' => !$this->parameters['languageUseCustomValue'] && empty($this->parameters['languageCustomItemValue']) ? null : $this->parameters['languageCustomItemValue'],
                'languageUseCustomValue' => $this->parameters['languageUseCustomValue'] ? 1 : 0,
                'languageCustomItemDisableTime' => null,
            ];

            if ($this->parameters['languageUseCustomValue']) {
                $updateData['languageItemOldValue'] = null;
            }
        } else {
            $updateData = [
                'languageItemValue' => $this->parameters['languageItemValue'],
            ];
        }
        $editor->update($updateData);

        // clear cache
        LanguageFactory::getInstance()->clearCache();
        LanguageFactory::getInstance()->deleteLanguageCache();

        $language = LanguageFactory::getInstance()->getLanguage($editor->languageID);
        EventHandler::getInstance()->fire(
            new PhraseChanged($language, $editor->languageItem)
        );
    }

    /**
     * Validates the `deleteCustomLanguageItems` action.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @since   5.2
     */
    public function validateDeleteCustomLanguageItems()
    {
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            throw new PermissionDeniedException();
        }

        $this->readObjects();
        if (empty($this->objects)) {
            throw new UserInputException('objectIDs');
        }

        // this method is only available for custom language items
        foreach ($this->getObjects() as $languageItem) {
            if (!$languageItem->isCustomLanguageItem) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Deletes custom language items in every language.
     *
     * @since   5.2
     */
    public function deleteCustomLanguageItems()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $languageItems = [];
        foreach ($this->getObjects() as $languageItem) {
            $languageItems[] = $languageItem->languageItem;
        }

        $languageItemList = new LanguageItemList();
        $languageItemList->getConditionBuilder()->add('isCustomLanguageItem = ?', [1]);
        $languageItemList->getConditionBuilder()->add('languageItem IN (?)', [\array_unique($languageItems)]);
        $languageItemList->readObjects();

        (new self($languageItemList->getObjects(), 'delete'))->executeAction();

        LanguageFactory::getInstance()->clearCache();
        LanguageFactory::getInstance()->deleteLanguageCache();
    }
}

<?php

namespace wcf\data\language\item;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\language\LanguageFactory;

/**
 * Executes language item-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<LanguageItem, LanguageItemEditor>
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
    protected $requireACP = ['create', 'delete', 'update'];

    /**
     * Creates multiple language items.
     *
     * @return void
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
                new self([], 'create', [
                    'data' => \array_merge(
                        $this->parameters['data'],
                        [
                            'languageID' => $languageID,
                            'languageItemValue' => $value,
                        ]
                    ),
                ])->executeAction();
            }
        } else {
            // single language
            new self([], 'create', [
                'data' => \array_merge(
                    $this->parameters['data'],
                    [
                        'languageID' => LanguageFactory::getInstance()->getDefaultLanguageID(),
                    ]
                ),
            ])->executeAction();
        }
    }
}

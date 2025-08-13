<?php

namespace wcf\data\user\ignore;

use wcf\command\user\IgnoreUser;
use wcf\command\user\UnignoreUser;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\DialogFormDocument;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes ignored user-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserIgnore, UserIgnoreEditor>
 */
class UserIgnoreAction extends AbstractDatabaseObjectAction
{
    /**
     * @var ?IFormDocument
     */
    protected $form;

    /**
     * Validates the 'ignore' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateIgnore()
    {
        $this->readInteger('userID', false, 'data');

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['data']['userID']);
        if ($userProfile === null || $userProfile->userID == WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        // check permissions
        if ($userProfile->getPermission('user.profile.cannotBeIgnored')) {
            throw new PermissionDeniedException();
        }

        $this->readInteger('type', true, 'data');

        if (
            $this->parameters['data']['type']
            && !\in_array($this->parameters['data']['type'], [
                UserIgnore::TYPE_BLOCK_DIRECT_CONTACT,
                UserIgnore::TYPE_HIDE_MESSAGES,
            ])
        ) {
            throw new UserInputException('type', 'invalid');
        }
    }

    /**
     * Ignores a user.
     *
     * @return array{isIgnoredUser: 1}
     *
     * @deprecated 6.3 use the `IgnoreUser` command instead.
     */
    public function ignore()
    {
        $type = $this->parameters['data']['type'] ?? UserIgnore::TYPE_BLOCK_DIRECT_CONTACT;

        (new IgnoreUser($this->parameters['data']['userID'], $type))();

        return ['isIgnoredUser' => 1];
    }

    /**
     * Validates the 'unignore' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateUnignore()
    {
        $this->readInteger('userID', false, 'data');

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['data']['userID']);
        if ($userProfile === null) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Unignores a user.
     *
     * @return array{isIgnoredUser: 0}
     *
     * @deprecated 6.3 use the `UnignoreUser` command instead.
     */
    public function unignore()
    {
        (new UnignoreUser($this->parameters['data']['userID']))();

        return ['isIgnoredUser' => 0];
    }

    /**
     * @return void
     */
    public function validateGetDialog()
    {
        $this->readInteger('userID');

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
        if ($userProfile === null || $userProfile->userID == WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        $ignore = UserIgnore::getIgnore($this->parameters['userID']);

        // Check if the user is not yet ignored and cannot be ignored.
        if (!$ignore->ignoreID && $userProfile->getPermission('user.profile.cannotBeIgnored')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return array{dialog: string, formId: string}
     */
    public function getDialog()
    {
        $form = $this->getForm();

        return [
            'dialog' => $form->getHtml(),
            'formId' => $form->getId(),
        ];
    }

    /**
     * @return void
     */
    public function validateSubmitDialog()
    {
        $this->validateGetDialog();

        $this->readString('formId');

        $this->getForm()->requestData($this->parameters['data'] ?? []);
        $this->getForm()->readValues();
        $this->getForm()->validate();
    }

    /**
     * @return array{dialog: string, formId: string}|array{isIgnoredUser: 0|1}
     */
    public function submitDialog()
    {
        if ($this->getForm()->hasValidationErrors()) {
            return [
                'dialog' => $this->getForm()->getHtml(),
                'formId' => $this->getForm()->getId(),
            ];
        }

        $formData = $this->getForm()->getData();

        if ($formData['data']['type'] === UserIgnore::TYPE_NO_IGNORE) {
            return (new self([], 'unignore', [
                'data' => [
                    'userID' => $this->parameters['userID'],
                ],
            ]))->executeAction()['returnValues'];
        } else {
            return (new self([], 'ignore', [
                'data' => [
                    'userID' => $this->parameters['userID'],
                    'type' => $formData['data']['type'],
                ],
            ]))->executeAction()['returnValues'];
        }
    }

    protected function getForm(): IFormDocument
    {
        if ($this->form === null) {
            $id = 'userIgnore';
            $this->form = DialogFormDocument::create($id)
                ->ajax()
                ->prefix($id);

            $ignore = UserIgnore::getIgnore($this->parameters['userID']);

            $this->form->appendChildren([
                RadioButtonFormField::create('type')
                    ->label(WCF::getLanguage()->get('wcf.user.ignore.type'))
                    ->options([
                        UserIgnore::TYPE_NO_IGNORE => WCF::getLanguage()
                            ->get('wcf.user.ignore.type.noIgnore'),
                        UserIgnore::TYPE_BLOCK_DIRECT_CONTACT => WCF::getLanguage()
                            ->get('wcf.user.ignore.type.blockDirectContact'),
                        UserIgnore::TYPE_HIDE_MESSAGES => WCF::getLanguage()
                            ->get('wcf.user.ignore.type.hideMessages'),
                    ])
                    ->value($ignore->type ?: 0)
                    ->required()
                    ->addValidator(new FormFieldValidator('type', function (RadioButtonFormField $formField) {
                        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
                        if ($userProfile->getPermission('user.profile.cannotBeIgnored')) {
                            if ($formField->getValue() != UserIgnore::TYPE_NO_IGNORE) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'cannotBeIgnored',
                                        'wcf.user.ignore.error.cannotBeIgnored'
                                    )
                                );
                            }
                        }
                    })),
            ]);

            $this->form->getDataHandler()->addProcessor(
                new CustomFormDataProcessor(
                    'type',
                    static function (IFormDocument $document, array $parameters) {
                        $parameters['data']['type'] = \intval($parameters['data']['type']);

                        return $parameters;
                    }
                )
            );

            $this->form->build();
        }

        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        // read objects
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        // validate ownership
        foreach ($this->getObjects() as $ignore) {
            if ($ignore->userID != WCF::getUser()->userID) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $userIDs = \array_map(function ($ignore) {
            return $ignore->ignoreUserID;
        }, $this->getObjects());

        $returnValues = parent::delete();

        // reset storage
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
        UserStorageHandler::getInstance()->reset($userIDs, 'ignoredByUserIDs');

        return $returnValues;
    }
}

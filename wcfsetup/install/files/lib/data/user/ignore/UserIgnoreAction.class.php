<?php

namespace wcf\data\user\ignore;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowEditor;
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
 * @method  UserIgnore      create()
 * @method  UserIgnoreEditor[]  getObjects()
 * @method  UserIgnoreEditor    getSingleObject()
 */
class UserIgnoreAction extends AbstractDatabaseObjectAction
{
    protected $form;

    /**
     * Validates the 'ignore' action.
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
     * @return  array
     */
    public function ignore()
    {
        $ignore = new UserIgnoreEditor(UserIgnore::getIgnore($this->parameters['data']['userID']));
        $type = $this->parameters['data']['type'] ?? UserIgnore::TYPE_BLOCK_DIRECT_CONTACT;

        if ($ignore->ignoreID) {
            $ignore->update([
                'type' => $type,
                'time' => TIME_NOW,
            ]);
        } else {
            $ignore = UserIgnoreEditor::createOrIgnore([
                'ignoreUserID' => $this->parameters['data']['userID'],
                'type' => $type,
                'time' => TIME_NOW,
                'userID' => WCF::getUser()->userID,
            ]);
        }

        if ($ignore !== null) {
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
            UserStorageHandler::getInstance()->reset([$this->parameters['data']['userID']], 'ignoredByUserIDs');

            // check if target user is following the current user
            $sql = "SELECT  *
                    FROM    wcf1_user_follow
                    WHERE   userID = ?
                        AND followUserID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->parameters['data']['userID'],
                WCF::getUser()->userID,
            ]);

            $follow = $statement->fetchObject(UserFollow::class);

            // remove follower
            if ($follow !== null) {
                $followEditor = new UserFollowEditor($follow);
                $followEditor->delete();

                // reset storage
                UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'followerUserIDs');
                UserStorageHandler::getInstance()->reset([$this->parameters['data']['userID']], 'followingUserIDs');
            }
        }

        return ['isIgnoredUser' => 1];
    }

    /**
     * Validates the 'unignore' action.
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
     * @return  array
     */
    public function unignore()
    {
        $ignore = UserIgnore::getIgnore($this->parameters['data']['userID']);

        if ($ignore->ignoreID) {
            $ignoreEditor = new UserIgnoreEditor($ignore);
            $ignoreEditor->delete();

            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
            UserStorageHandler::getInstance()->reset([$this->parameters['data']['userID']], 'ignoredByUserIDs');
        }

        return ['isIgnoredUser' => 0];
    }

    public function validateGetDialog()
    {
        $this->readInteger('userID');

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
        if ($userProfile === null || $userProfile->userID == WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        $ignore = UserIgnore::getIgnore($this->parameters['userID']);

        // Check if the user is not yet ignored and cannot be ignored.
        if (!$ignore && $userProfile->getPermission('user.profile.cannotBeIgnored')) {
            throw new PermissionDeniedException();
        }
    }

    public function getDialog()
    {
        $form = $this->getForm();

        return [
            'dialog' => $form->getHtml(),
            'formId' => $form->getId(),
        ];
    }

    public function validateSubmitDialog()
    {
        $this->validateGetDialog();

        $this->readString('formId');

        $this->getForm()->requestData($this->parameters['data'] ?? []);
        $this->getForm()->readValues();
        $this->getForm()->validate();
    }

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

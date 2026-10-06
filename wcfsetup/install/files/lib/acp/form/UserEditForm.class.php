<?php

namespace wcf\acp\form;

use wcf\data\style\Style;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\form\AbstractForm;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\style\StyleHandler;
use wcf\command\user\SetColorScheme;
use wcf\system\user\multifactor\Setup;
use wcf\system\view\user\profile\AdminUserProfileHeaderView;
use wcf\system\WCF;

/**
 * Shows the user edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserEditForm extends UserAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canEditUser'];

    /**
     * user id
     * @var int
     */
    public $userID = 0;

    /**
     * user editor object
     * @var ?UserEditor
     */
    public $user;

    /**
     * true to disable multifactor authentication
     * @var int
     */
    public $multifactorDisable = 0;

    /**
     * list of available styles for the edited user
     * @var         Style[]
     * @since       5.3
     */
    public $availableStyles = [];

    /**
     * id of the used style
     * @var         int
     * @since       5.3
     */
    public $styleID = 0;

    /**
     * @since 6.0
     */
    public string $colorScheme = 'system';

    #[\Override]
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->userID = \intval($_REQUEST['id']);
        }
        $user = new User($this->userID);
        if ($user->isGuest()) {
            throw new IllegalLinkException();
        }

        $this->user = new UserEditor($user);
        if (!UserGroup::isAccessibleGroup($this->user->getGroupIDs())) {
            throw new PermissionDeniedException();
        }
        $this->attachmentObjectID = $this->user->userID;

        parent::readParameters();
    }

    #[\Override]
    protected function initOptionHandler()
    {
        $this->optionHandler->setUser($this->user->getDecoratedObject());
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (!WCF::getSession()->hasPermission('admin.user.canEditPassword') || !empty($this->user->authData)) {
            $this->password = '';
        }
        if (!WCF::getSession()->hasPermission('admin.user.canEditMailAddress')) {
            $this->email = $this->user->email;
        }

        if (isset($_POST['styleID'])) {
            $this->styleID = \intval($_POST['styleID']);
        }
        if (isset($_POST['colorScheme'])) {
            $this->colorScheme = $_POST['colorScheme'];
        }

        if (WCF::getSession()->hasPermission('admin.user.canEditPassword') && isset($_POST['multifactorDisable'])) {
            $this->multifactorDisable = 1;
        }
    }

    #[\Override]
    public function readData()
    {
        if ($_POST === []) {
            // get visible languages
            $this->readVisibleLanguages();

            // default values
            $this->readDefaultValues();
        }

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        foreach (StyleHandler::getInstance()->getStyles() as $style) {
            if ($style->isDisabled === 0 || $userProfile->hasPermission('admin.style.canUseDisabledStyle')) {
                $this->availableStyles[$style->styleID] = $style;
            }
        }

        parent::readData();
    }

    /**
     * Sets the selected languages.
     *
     * @return void
     */
    protected function readVisibleLanguages()
    {
        $this->visibleLanguages = $this->user->getLanguageIDs();
    }

    /**
     * Sets the default values.
     *
     * @return void
     */
    protected function readDefaultValues()
    {
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->groupIDs = $this->user->getGroupIDs(true);
        $this->languageID = $this->user->languageID;
        $this->userTitle = $this->user->userTitle;
        $this->styleID = $this->user->styleID;

        $this->signature = $this->user->signature;
        $this->disableSignature = $this->user->disableSignature;
        $this->disableSignatureReason = $this->user->disableSignatureReason;
        $this->disableSignatureExpires = $this->user->disableSignatureExpires;

        $this->colorScheme = $this->user->getUserOption('colorScheme') ?? 'system';
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        $signatureProcessor = new HtmlUpcastProcessor();
        $signatureProcessor->process($this->signature ?: '', 'com.woltlab.wcf.user.signature', $this->user->userID);

        WCF::getTPL()->assign([
            'userProfileHeaderView' => new AdminUserProfileHeaderView(
                UserProfileRuntimeCache::getInstance()->getObject($this->userID)
            ),
            'userID' => $this->user->userID,
            'action' => 'edit',
            'url' => '',
            'user' => $this->user,
            'ownerGroupID' => UserGroup::getOwnerGroupID(),
            'availableStyles' => $this->availableStyles,
            'styleID' => $this->styleID,
            'colorScheme' => $this->colorScheme,
            'signature' => $signatureProcessor->getHtml(),
        ]);
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();
        $this->htmlInputProcessor->setObjectID($this->userID);
        MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor);

        // add default groups
        $defaultGroups = UserGroup::getAccessibleGroups([UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
        $oldGroupIDs = $this->user->getGroupIDs(true);
        foreach ($oldGroupIDs as $oldGroupID) {
            if (isset($defaultGroups[$oldGroupID])) {
                $this->groupIDs[] = $oldGroupID;
            }
        }
        $this->groupIDs = \array_unique($this->groupIDs);

        // save user
        $saveOptions = $this->optionHandler->save();

        $data = [
            'data' => \array_merge($this->additionalFields, [
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
                'languageID' => $this->languageID,
                'userTitle' => $this->userTitle,
                'signature' => $this->htmlInputProcessor->getHtml(),
                'signatureEnableHtml' => 1,
                'styleID' => $this->styleID,
            ]),
            'groups' => $this->groupIDs,
            'languageIDs' => $this->visibleLanguages,
            'options' => $saveOptions,
            'signatureAttachmentHandler' => $this->attachmentHandler,
        ];
        // handle changed username
        if (\mb_strtolower($this->username) !== \mb_strtolower($this->user->username)) {
            $data['data']['lastUsernameChange'] = \TIME_NOW;
            $data['data']['oldUsername'] = $this->user->username;
        }

        // handle disabled signature
        if (WCF::getSession()->hasPermission('admin.user.canDisableSignature')) {
            $data['data']['disableSignature'] = $this->disableSignature;
            $data['data']['disableSignatureReason'] = $this->disableSignatureReason;
            $data['data']['disableSignatureExpires'] = $this->disableSignatureExpires;
        }

        $this->objectAction = new UserAction([$this->userID], 'update', $data);
        $this->objectAction->executeAction();

        // disable multifactor authentication
        if (WCF::getSession()->hasPermission('admin.user.canEditPassword') && $this->multifactorDisable !== 0) {
            WCF::getDB()->beginTransaction();
            $setups = Setup::getAllForUser($this->user->getDecoratedObject());
            foreach ($setups as $setup) {
                $setup->delete();
            }

            $this->user->update([
                'multifactorActive' => 0,
            ]);
            WCF::getDB()->commitTransaction();
        }

        if ($this->user->getUserOption('colorScheme') !== $this->colorScheme) {
            new SetColorScheme($this->user->getDecoratedObject(), $this->colorScheme)();
        }

        // reload user
        $this->user = new UserEditor(new User($this->userID));

        // update user rank
        if (\MODULE_USER_RANK !== 0) {
            $action = new UserProfileAction([$this->user], 'updateUserRank');
            $action->executeAction();
        }
        if (\MODULE_USERS_ONLINE !== 0) {
            $action = new UserProfileAction([$this->user], 'updateUserOnlineMarking');
            $action->executeAction();
        }

        // remove assignments
        $sql = "DELETE FROM wcf1_moderation_queue_to_user
                WHERE       userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->user->userID]);

        // reset moderation count
        ModerationQueueManager::getInstance()->resetModerationCount($this->user->userID);
        $this->saved();

        // reset password
        $this->password = '';

        // reload user
        $this->user = new UserEditor(new User($this->userID));

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    protected function validateUsername(string $username)
    {
        try {
            if (\mb_strtolower($this->user->username) !== \mb_strtolower($username)) {
                parent::validateUsername($username);
            }
        } catch (UserInputException $e) {
            if ($e->getField() === 'username' && $e->getType() === 'notUnique') {
                $user2 = User::getUserByUsername($username);
                if ($user2->userID !== $this->user->userID) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
    }

    #[\Override]
    protected function validateEmail(string $email): void
    {
        if (\mb_strtolower($this->user->email) !== \mb_strtolower($email)) {
            parent::validateEmail($email);
        }
    }

    #[\Override]
    protected function validatePassword(
        #[\SensitiveParameter]
        string $password
    ): void {
        if (!empty($password)) {
            parent::validatePassword($password);
        }
    }

    #[\Override]
    public function validate()
    {
        if ($this->user->userID === WCF::getUser()->userID && WCF::getUser()->hasOwnerAccess()) {
            $ownerGroupID = UserGroup::getOwnerGroupID();
            if ($ownerGroupID !== null && !\in_array($ownerGroupID, $this->groupIDs, true)) {
                // Members of the owner group cannot remove themselves.
                throw new PermissionDeniedException();
            }
        }

        parent::validate();

        if (!isset($this->availableStyles[$this->styleID])) {
            $this->styleID = 0;
        }

        if ($this->colorScheme !== 'light' && $this->colorScheme !== 'dark') {
            $this->colorScheme = 'system';
        }
    }
}

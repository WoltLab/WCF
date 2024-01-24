<?php

namespace wcf\acp\form;

use wcf\data\smiley\category\SmileyCategory;
use wcf\data\smiley\SmileyCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\form\AbstractForm;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the user add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserAddForm extends UserOptionListForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canAddUser'];

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * email address
     * @var string
     */
    public $email = '';

    /**
     * confirmed email address
     * @var string
     */
    public $confirmEmail = '';

    /**
     * user password
     * @var string
     */
    public $password = '';

    /**
     * confirmed user password
     * @var string
     */
    public $confirmPassword = '';

    /**
     * user group ids
     * @var int[]
     */
    public $groupIDs = [];

    /**
     * @var HtmlInputProcessor
     */
    public $htmlInputProcessor;

    /**
     * language id
     * @var int
     */
    public $languageID = 0;

    /**
     * visible languages
     * @var int[]
     */
    public $visibleLanguages = [];

    /**
     * title of the user
     * @var string
     */
    protected $userTitle = '';

    /**
     * signature text
     * @var string
     */
    public $signature = '';

    /**
     * true to disable this signature
     * @var bool
     */
    public $disableSignature = 0;

    /**
     * reason
     * @var string
     */
    public $disableSignatureReason = '';

    /**
     * date when the signature will be enabled again
     * @var int
     */
    public $disableSignatureExpires = 0;

    /**
     * tree of available user options
     * @var array
     */
    public $optionTree = [];
    public AttachmentHandler $attachmentHandler;
    public int $attachmentObjectID = 0;
    public string $attachmentObjectType = 'com.woltlab.wcf.user.signature';
    public array $defaultSmilies = [];
    /**
     * list of smiley categories
     * @var SmileyCategory[]
     */
    public array $smileyCategories = [];
    public ?string $tmpHash = '';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['tmpHash'])) {
            $this->tmpHash = $_REQUEST['tmpHash'];
        }
        if (empty($this->tmpHash)) {
            /** @deprecated 5.5 see QuickReplyManager::setTmpHash() */
            $this->tmpHash = WCF::getSession()->getVar('__wcfAttachmentTmpHash');
            if ($this->tmpHash === null) {
                $this->tmpHash = StringUtil::getRandomID();
            } else {
                WCF::getSession()->unregister('__wcfAttachmentTmpHash');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['username'])) {
            $this->username = StringUtil::trim($_POST['username']);
        }
        if (isset($_POST['email'])) {
            $this->email = StringUtil::trim($_POST['email']);
        }
        if (isset($_POST['confirmEmail'])) {
            $this->confirmEmail = StringUtil::trim($_POST['confirmEmail']);
        }
        if (isset($_POST['password'])) {
            $this->password = $_POST['password'];
        }
        if (isset($_POST['confirmPassword'])) {
            $this->confirmPassword = $_POST['confirmPassword'];
        }
        if (isset($_POST['groupIDs']) && \is_array($_POST['groupIDs'])) {
            $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
        }
        if (isset($_POST['visibleLanguages']) && \is_array($_POST['visibleLanguages'])) {
            $this->visibleLanguages = ArrayUtil::toIntegerArray($_POST['visibleLanguages']);
        }
        if (isset($_POST['languageID'])) {
            $this->languageID = \intval($_POST['languageID']);
        }
        if (isset($_POST['userTitle'])) {
            $this->userTitle = $_POST['userTitle'];
        }

        if (isset($_POST['signature'])) {
            $this->signature = StringUtil::trim($_POST['signature']);
        }

        if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
            if (isset($_POST['disableSignatureReason'])) {
                $this->disableSignatureReason = StringUtil::trim($_POST['disableSignatureReason']);
            }
            if (!empty($_POST['disableSignature'])) {
                $this->disableSignature = 1;
            }
            if ($this->disableSignature && !isset($_POST['disableSignatureNeverExpires'])) {
                if (isset($_POST['disableSignatureExpires'])) {
                    $this->disableSignatureExpires = @\intval(@\strtotime(StringUtil::trim($_POST['disableSignatureExpires'])));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // validate static user options
        try {
            $this->validateUsername($this->username);
        } catch (UserInputException $e) {
            $this->errorType[$e->getField()] = $e->getType();
        }

        try {
            $this->validateEmail($this->email, $this->confirmEmail);
        } catch (UserInputException $e) {
            $this->errorType[$e->getField()] = $e->getType();
        }

        try {
            $this->validatePassword($this->password, $this->confirmPassword);
        } catch (UserInputException $e) {
            $this->errorType[$e->getField()] = $e->getType();
        }

        // validate user groups
        if (!empty($this->groupIDs)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("groupID IN (?)", [$this->groupIDs]);
            $conditions->add("groupType NOT IN (?)", [[UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]]);

            $sql = "SELECT  groupID
                    FROM    wcf" . WCF_N . "_user_group
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
            $this->groupIDs = [];
            while ($row = $statement->fetchArray()) {
                if (UserGroup::isAccessibleGroup([$row['groupID']])) {
                    $this->groupIDs[] = $row['groupID'];
                }
            }
        }

        // validate user language
        $language = LanguageFactory::getInstance()->getLanguage($this->languageID);
        if ($language === null || !$language->languageID) {
            // use default language
            $this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        }

        // validate visible languages
        foreach ($this->visibleLanguages as $key => $visibleLanguage) {
            $language = LanguageFactory::getInstance()->getLanguage($visibleLanguage);
            if (!$language->languageID || !$language->hasContent) {
                unset($this->visibleLanguages[$key]);
            }
        }
        if (empty($this->visibleLanguages) && ($language = LanguageFactory::getInstance()->getLanguage($this->languageID)) && $language->hasContent) {
            $this->visibleLanguages[] = $this->languageID;
        }

        // validate user title
        try {
            if (\mb_strlen($this->userTitle) > USER_TITLE_MAX_LENGTH) {
                throw new UserInputException('userTitle', 'tooLong');
            }
            if (!StringUtil::executeWordFilter($this->userTitle, USER_FORBIDDEN_TITLES)) {
                throw new UserInputException('userTitle', 'forbidden');
            }
        } catch (UserInputException $e) {
            $this->errorType[$e->getField()] = $e->getType();
        }

        // validate signature
        $this->htmlInputProcessor = new HtmlInputProcessor();
        $this->htmlInputProcessor->process($this->signature, 'com.woltlab.wcf.user.signature', 0);

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission('user.signature.disallowedBBCodes')
        ));
        $disallowedBBCodes = $this->htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
            throw new UserInputException('signature', 'disallowedBBCodes');
        }

        // validate dynamic options
        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // create
        $saveOptions = $this->optionHandler->save();
        $this->additionalFields['languageID'] = $this->languageID;
        $data = [
            'data' => \array_merge($this->additionalFields, [
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
                'userTitle' => $this->userTitle,
                'signature' => $this->htmlInputProcessor->getHtml(),
                'signatureEnableHtml' => 1,
            ]),
            'groups' => $this->groupIDs,
            'languageIDs' => $this->visibleLanguages,
            'options' => $saveOptions,
            'signatureAttachmentHandler' => $this->attachmentHandler,
        ];

        if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
            $data['data']['disableSignature'] = $this->disableSignature;
            $data['data']['disableSignatureReason'] = $this->disableSignatureReason;
            $data['data']['disableSignatureExpires'] = $this->disableSignatureExpires;
        }

        $this->objectAction = new UserAction([], 'create', $data);
        $returnValues = $this->objectAction->executeAction();

        $this->htmlInputProcessor->setObjectID($returnValues['returnValues']->userID);
        MessageEmbeddedObjectManager::getInstance()->registerObjects($this->htmlInputProcessor);
        $this->attachmentHandler->updateObjectID($returnValues['returnValues']->userID);

        $this->saved();

        // show empty add form
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                UserEditForm::class,
                ['id' => $returnValues['returnValues']->userID]
            ),
        ]);

        // reset values
        $this->disableSignature = $this->disableSignatureExpires = 0;
        $this->username = $this->email = $this->confirmEmail = $this->password = $this->confirmPassword = $this->userTitle = '';
        $this->signature = $this->disableSignatureReason = '';
        $this->groupIDs = [];
        $this->languageID = $this->getDefaultFormLanguageID();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->optionHandler->resetOptionValues();
        // Reload attachment handler to reset the uploaded attachments.
        $this->attachmentHandler = new AttachmentHandler(
            $this->attachmentObjectType,
            $this->attachmentObjectID,
            $this->tmpHash
        );
    }

    /**
     * Throws a UserInputException if the username is not unique or not valid.
     *
     * @param string $username
     * @throws  UserInputException
     */
    protected function validateUsername($username)
    {
        if (empty($username)) {
            throw new UserInputException('username');
        }

        // check for forbidden chars (e.g. the ",")
        if (!UserUtil::isValidUsername($username)) {
            throw new UserInputException('username', 'invalid');
        }

        // Check if username exists already.
        if (User::getUserByUsername($username)->userID) {
            throw new UserInputException('username', 'notUnique');
        }
    }

    /**
     * Throws a UserInputException if the email is not unique or not valid.
     *
     * @param string $email
     * @param string $confirmEmail
     * @throws  UserInputException
     */
    protected function validateEmail($email, $confirmEmail)
    {
        if (empty($email)) {
            throw new UserInputException('email');
        }

        // check for valid email (one @ etc.)
        if (!UserUtil::isValidEmail($email)) {
            throw new UserInputException('email', 'invalid');
        }

        // Check if email exists already.
        if (User::getUserByEmail($email)->userID) {
            throw new UserInputException('email', 'notUnique');
        }

        // check confirm input
        if (\mb_strtolower($email) != \mb_strtolower($confirmEmail)) {
            throw new UserInputException('confirmEmail', 'notEqual');
        }
    }

    /**
     * Throws a UserInputException if the password is not valid.
     *
     * @param string $password
     * @param string $confirmPassword
     * @throws  UserInputException
     */
    protected function validatePassword(
        #[\SensitiveParameter]
        $password,
        #[\SensitiveParameter]
        $confirmPassword
    ) {
        if (empty($password)) {
            throw new UserInputException('password');
        }

        // check confirm input
        if ($password != $confirmPassword) {
            throw new UserInputException('confirmPassword', 'notEqual');
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $this->attachmentHandler = new AttachmentHandler(
            $this->attachmentObjectType,
            $this->attachmentObjectID,
            $this->tmpHash,
            0
        );

        parent::readData();
        // get default smilies
        if (MODULE_SMILEY) {
            $this->smileyCategories = SmileyCache::getInstance()->getVisibleCategories();

            $firstCategory = \reset($this->smileyCategories);
            if ($firstCategory) {
                $this->defaultSmilies = SmileyCache::getInstance()
                    ->getCategorySmilies($firstCategory->categoryID ?: null);
            }
        }

        $this->readOptionTree();
    }

    /**
     * Reads option tree on page init.
     */
    protected function readOptionTree()
    {
        $this->optionTree = $this->optionHandler->getOptionTree();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'username' => $this->username,
            'email' => $this->email,
            'confirmEmail' => $this->confirmEmail,
            'password' => $this->password,
            'confirmPassword' => $this->confirmPassword,
            'groupIDs' => $this->groupIDs,
            'optionTree' => $this->optionTree,
            'availableGroups' => $this->getAvailableGroups(),
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'languageID' => $this->languageID,
            'visibleLanguages' => $this->visibleLanguages,
            'availableContentLanguages' => LanguageFactory::getInstance()->getContentLanguages(),
            'action' => 'add',
            'userTitle' => $this->userTitle,
            'signature' => $this->signature,
            'disableSignature' => $this->disableSignature,
            'disableSignatureReason' => $this->disableSignatureReason,
            'disableSignatureExpires' => $this->disableSignatureExpires,
            'attachmentHandler' => $this->attachmentHandler,
            'attachmentObjectID' => $this->attachmentObjectID,
            'attachmentObjectType' => $this->attachmentObjectType,
            'attachmentParentObjectID' => 0,
            'defaultSmilies' => $this->defaultSmilies,
            'smileyCategories' => $this->smileyCategories,
            'tmpHash' => $this->tmpHash,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // get the default language id
        $this->languageID = $this->getDefaultFormLanguageID();

        // show form
        parent::show();
    }
}

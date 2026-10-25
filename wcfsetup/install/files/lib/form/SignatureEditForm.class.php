<?php

namespace wcf\form;

use wcf\command\user\UpdateUser;
use wcf\data\user\UserBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\signature\SignatureCache;
use wcf\system\WCF;

/**
 * Shows the signature edit form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SignatureEditForm extends MessageForm
{
    /**
     * @inheritDoc
     */
    public $disallowedBBCodesPermission = 'user.signature.disallowedBBCodes';

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $messageObjectType = 'com.woltlab.wcf.user.signature';

    /**
     * @inheritDoc
     */
    public $attachmentObjectType = 'com.woltlab.wcf.user.signature';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_USER_SIGNATURE'];

    /**
     * @var bool
     */
    public $showSignatureSetting = false;

    /**
     * parsed signature cache
     * @var ?string
     */
    public $signatureCache;

    /**
     * @inheritDoc
     */
    public $templateName = 'signatureEdit';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.signature.canEditSignature'];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // get max text length
        $this->maxTextLength = WCF::getSession()->getPermission('user.signature.maxLength');
        $this->attachmentObjectID = WCF::getUser()->userID;
    }

    #[\Override]
    public function validate()
    {
        if (WCF::getUser()->disableSignature !== 0) {
            throw new PermissionDeniedException();
        }

        AbstractForm::validate();

        if (!empty($this->text)) {
            $this->validateText();
        } else {
            $this->htmlInputProcessor = new HtmlInputProcessor();
            $this->htmlInputProcessor->process($this->text, $this->messageObjectType, WCF::getUser()->userID);
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        // default values
        if ($_POST === []) {
            $this->text = WCF::getUser()->signature ?? '';
        }

        $this->signatureCache = SignatureCache::getInstance()->getSignature(WCF::getUser());
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'signatureCache' => $this->signatureCache,
        ]);
    }

    #[\Override]
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.signature');

        parent::show();
    }

    #[\Override]
    public function save()
    {
        parent::save();

        $builder = UserBuilder::forUpdate(WCF::getUser())
            ->setSignatureHtmlInputProcessor($this->htmlInputProcessor)
            ->setSignatureEnableHtml(true);
        foreach ($this->additionalFields as $name => $value) {
            $builder->setCustomProperty($name, $value);
        }
        $user = new UpdateUser($builder)();
        SignatureCache::getInstance()->getSignature($user);
        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }
}

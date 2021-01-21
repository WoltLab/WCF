<?php

namespace wcf\acp\form;

use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\user\authentication\failure\UserAuthenticationFailureAction;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\form\AbstractCaptchaForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\system\user\authentication\EmailUserAuthentication;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the acp login form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
class LoginForm extends AbstractCaptchaForm
{
    /**
     * given login username
     * @var string
     */
    public $username = '';

    /**
     * given login password
     * @var string
     */
    public $password = '';

    /**
     * user object
     * @var User
     */
    public $user;

    /**
     * given forward url
     * @var string
     */
    public $url;

    /**
     * @inheritDoc
     */
    public $useCaptcha = false;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['url'])) {
            $this->url = StringUtil::trim($_REQUEST['url']);

            // discard URL if it is not an absolute URL of local content
            if (!ApplicationHandler::getInstance()->isInternalURL($this->url)) {
                $this->url = '';
            }
        }

        if (WCF::getUser()->userID) {
            // User is already logged in
            $this->performRedirect();
        }

        // check authentication failures
        if (ENABLE_USER_AUTHENTICATION_FAILURE) {
            $failures = UserAuthenticationFailure::countIPFailures(UserUtil::getIpAddress());
            if (USER_AUTHENTICATION_FAILURE_IP_BLOCK && $failures >= USER_AUTHENTICATION_FAILURE_IP_BLOCK) {
                throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.login.blocked'));
            }
            if (USER_AUTHENTICATION_FAILURE_IP_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_IP_CAPTCHA) {
                $this->useCaptcha = true;
            } elseif (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
                if (isset($_POST['username'])) {
                    $user = User::getUserByUsername(StringUtil::trim($_POST['username']));
                    if (!$user->userID) {
                        $user = User::getUserByEmail(StringUtil::trim($_POST['username']));
                    }

                    if ($user->userID) {
                        $failures = UserAuthenticationFailure::countUserFailures($user->userID);
                        if (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
                            $this->useCaptcha = true;
                        }
                    }
                }
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
        if (isset($_POST['password'])) {
            $this->password = $_POST['password'];
        }
    }

    /**
     * Validates the user access data.
     */
    protected function validateUser()
    {
        try {
            $this->user = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginManually($this->username, $this->password);
        } catch (UserInputException $e) {
            if ($e->getField() == 'username') {
                try {
                    $this->user = EmailUserAuthentication::getInstance()->loginManually($this->username, $this->password);
                } catch (UserInputException $e2) {
                    if ($e2->getField() == 'username') {
                        throw $e;
                    }
                    throw $e2;
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function submit()
    {
        parent::submit();

        // save authentication failure
        if (ENABLE_USER_AUTHENTICATION_FAILURE) {
            if ($this->errorField == 'username' || $this->errorField == 'password') {
                $user = User::getUserByUsername($this->username);
                if (!$user->userID) {
                    $user = User::getUserByEmail($this->username);
                }

                $action = new UserAuthenticationFailureAction([], 'create', [
                    'data' => [
                        'environment' => RequestHandler::getInstance()->isACPRequest() ? 'admin' : 'user',
                        'userID' => $user->userID ?: null,
                        'username' => \mb_substr($this->username, 0, 100),
                        'time' => TIME_NOW,
                        'ipAddress' => UserUtil::getIpAddress(),
                        'userAgent' => UserUtil::getUserAgent(),
                    ],
                ]);
                $action->executeAction();

                if ($this->captchaObjectType) {
                    $this->captchaObjectType->getProcessor()->reset();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (!WCF::getSession()->hasValidCookie()) {
            throw new UserInputException('cookie');
        }

        // error handling
        if (empty($this->username)) {
            throw new UserInputException('username');
        }

        if (empty($this->password)) {
            throw new UserInputException('password');
        }

        $this->validateUser();

        if (RequestHandler::getInstance()->isACPRequest() && $this->user !== null) {
            $userProfile = new UserProfile($this->user);
            if (!$userProfile->getPermission('admin.general.canUseAcp')) {
                throw new UserInputException('username', 'acpNotAuthorized');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // change user
        $needsMultifactor = WCF::getSession()->changeUserAfterMultifactorAuthentication($this->user);
        if (!$needsMultifactor) {
            WCF::getSession()->registerReauthentication();
        }
        $this->saved();

        $this->performRedirect($needsMultifactor);
    }

    /**
     * Performs the redirect after successful authentication.
     */
    protected function performRedirect(bool $needsMultifactor = false)
    {
        if ($needsMultifactor) {
            $this->url = LinkHandler::getInstance()->getLink('MultifactorAuthentication', [
                'url' => $this->url,
            ]);
        }

        if (!empty($this->url)) {
            HeaderUtil::redirect($this->url);
        } else {
            if (RequestHandler::getInstance()->inRescueMode()) {
                $path = RouteHandler::getHost() . RouteHandler::getPath();
            } else {
                $application = ApplicationHandler::getInstance()->getActiveApplication();
                $path = $application->getPageURL() . 'acp/';
            }

            HeaderUtil::redirect($path);
        }

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'username' => $this->username,
            'password' => $this->password,
            'url' => $this->url,
        ]);
    }
}

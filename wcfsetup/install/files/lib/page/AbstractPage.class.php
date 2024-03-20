<?php

namespace wcf\page;

use Psr\Http\Message\ResponseInterface;
use wcf\data\page\PageCache;
use wcf\form\EmailActivationForm;
use wcf\form\EmailNewActivationCodeForm;
use wcf\form\LoginForm;
use wcf\form\LostPasswordForm;
use wcf\form\NewPasswordForm;
use wcf\form\RegisterActivationForm;
use wcf\form\RegisterForm;
use wcf\form\RegisterNewActivationCodeForm;
use wcf\page\DisclaimerPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\MetaTagHandler;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Abstract implementation of a page which fires the default event actions of a
 * page:
 *  - readParameters
 *  - readData
 *  - assignVariables
 *  - show
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractPage implements IPage
{
    /**
     * name of the active menu item
     * @var string
     */
    public $activeMenuItem = '';

    /**
     * value of the given action parameter
     * @var string
     */
    public $action = '';

    /**
     * canonical URL of this page
     * @var string
     */
    public $canonicalURL = '';

    /**
     * is true if canonical URL will be enforced even if POST data is represent
     * @var bool
     */
    public $forceCanonicalURL = false;

    /**
     * is true if the redirect should use a 307 instead of the default 301, not recommended in general
     * @var bool
     */
    public $softRedirectCanonicalURL = false;

    /**
     * indicates if you need to be logged in to access this page
     * @var bool
     */
    public $loginRequired = false;

    /**
     * needed modules to view this page
     * @var string[]
     */
    public $neededModules = [];

    /**
     * needed permissions to view this page
     * @var string[]
     */
    public $neededPermissions = [];

    /**
     * name of the template for the called page
     * @var string
     */
    public $templateName = '';

    /**
     * abbreviation of the application the template belongs to
     * @var string
     */
    public $templateNameApplication = '';

    /**
     * enables template usage
     * @var string
     */
    public $useTemplate = true;

    /**
     * @var ?ResponseInterface
     * @since 5.5
     */
    private $psr7Response;

    /**
     * @inheritDoc
     */
    final public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function __run()
    {
        $this->maybeSetPsr7Response(
            $this->readParameters()
        );
        if ($this->hasPsr7Response()) {
            return $this->getPsr7Response();
        }

        $this->maybeSetPsr7Response(
            $this->show()
        );
        if ($this->hasPsr7Response()) {
            return $this->getPsr7Response();
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        // call readParameters event
        EventHandler::getInstance()->fireAction($this, 'readParameters');

        // read action parameter
        if (isset($_REQUEST['action'])) {
            $this->action = $_REQUEST['action'];
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // call readData event
        EventHandler::getInstance()->fireAction($this, 'readData');

        $this->addPageDescriptionMetaTag();
    }

    /**
     * @since 5.4
     */
    private function addPageDescriptionMetaTag(): void
    {
        $page = PageCache::getInstance()->getPageByController(static::class);
        if ($page !== null) {
            $metaDescription = PageCache::getInstance()->getPageMetaDescription($page->pageID);
            if (!empty($metaDescription)) {
                $found = false;
                foreach (MetaTagHandler::getInstance() as $key => $metaTag) {
                    if ($key === "og:description") {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    MetaTagHandler::getInstance()->addTag(
                        "og:description",
                        "og:description",
                        $metaDescription
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        // call assignVariables event
        EventHandler::getInstance()->fireAction($this, 'assignVariables');

        // assign parameters
        WCF::getTPL()->assign([
            'action' => $this->action,
            'templateName' => $this->templateName,
            'templateNameApplication' => $this->templateNameApplication,
            'canonicalURL' => $this->canonicalURL,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function checkModules()
    {
        // call checkModules event
        EventHandler::getInstance()->fireAction($this, 'checkModules');

        // check modules
        foreach ($this->neededModules as $module) {
            if (!\defined($module) || !\constant($module)) {
                throw new IllegalLinkException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        // call checkPermissions event
        EventHandler::getInstance()->fireAction($this, 'checkPermissions');

        // check permission, it is sufficient to have at least one permission
        if (!empty($this->neededPermissions)) {
            $hasPermissions = false;
            foreach ($this->neededPermissions as $permission) {
                if (WCF::getSession()->getPermission($permission)) {
                    $hasPermissions = true;
                    break;
                }
            }

            if (!$hasPermissions) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        if (FORCE_LOGIN && !RequestHandler::getInstance()->isACPRequest() && !WCF::getUser()->userID) {
            $this->forceLogin();
        }

        // check if active user is logged in
        if ($this->loginRequired && !WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        $this->checkModules();

        $this->checkPermissions();

        // check if current request URL matches the canonical URL
        if ($this->canonicalURL && (empty($_POST) || $this->forceCanonicalURL)) {
            $canonicalURL = Url::parse($this->canonicalURL);

            // use $_SERVER['REQUEST_URI'] because it represents the URL used to access the site and not the internally rewritten one
            // IIS Rewrite-Module has a bug causing the REQUEST_URI to be ISO-encoded
            $requestURI = (!empty($_SERVER['UNENCODED_URL'])) ? $_SERVER['UNENCODED_URL'] : $_SERVER['REQUEST_URI'];

            if (!StringUtil::isUTF8($requestURI)) {
                $requestURI = \mb_convert_encoding($requestURI, 'UTF-8', 'ISO-8859-1');
            }

            // some webservers output lower-case encoding (e.g. %c3 instead of %C3)
            $requestURI = \preg_replace_callback('~%(?P<encoded>[a-zA-Z0-9]{2})~', static function ($matches) {
                return '%' . \strtoupper($matches['encoded']);
            }, $requestURI);

            // reduce successive forwarded slashes into a single one
            $requestURI = \preg_replace('~/{2,}~', '/', $requestURI);

            $requestURL = Url::parse($requestURI);
            $redirect = false;
            if ($canonicalURL['path'] != $requestURL['path']) {
                $redirect = true;
            } elseif (isset($canonicalURL['query'])) {
                if (!isset($requestURL['query'])) {
                    $redirect = true;
                } else {
                    \parse_str($canonicalURL['query'], $cQueryString);
                    \parse_str($requestURL['query'], $rQueryString);

                    foreach ($cQueryString as $key => $value) {
                        if (!isset($rQueryString[$key]) || $rQueryString[$key] != $value) {
                            $redirect = true;
                            break;
                        }
                    }
                }
            }

            if ($redirect) {
                $redirectURL = $this->canonicalURL;
                if (!empty($requestURL['query'])) {
                    \parse_str($requestURL['query'], $rQueryString);

                    if (!empty($canonicalURL['query'])) {
                        \parse_str($canonicalURL['query'], $cQueryString);

                        // clean query string
                        foreach ($cQueryString as $key => $value) {
                            if (isset($rQueryString[$key])) {
                                unset($rQueryString[$key]);
                            }
                        }
                    }

                    // drop route data from query
                    foreach ($rQueryString as $key => $value) {
                        if ($value === '') {
                            unset($rQueryString[$key]);
                        }
                    }

                    if (!empty($rQueryString)) {
                        $redirectURL .= !\str_contains($redirectURL, '?') ? '?' : '&';
                        $redirectURL .= \http_build_query($rQueryString, '', '&');
                    }
                }

                // force a permanent redirect as recommended by Google
                // https://support.google.com/webmasters/answer/6033086?hl=en#a_note_about_redirects
                HeaderUtil::redirect($redirectURL, true, $this->softRedirectCanonicalURL);

                exit;
            }
        }

        $this->setActiveMenuItem();

        $this->maybeSetPsr7Response(
            $this->readData()
        );

        // readData() calls submit() in AbstractForm. It might be desirable to be able
        // to return redirect responses after successfully submitting a form.
        if ($this->hasPsr7Response()) {
            return;
        }

        $this->assignVariables();

        EventHandler::getInstance()->fireAction($this, 'show');

        // try to guess template name
        $classParts = \explode('\\', static::class);
        if (empty($this->templateName)) {
            $className = \preg_replace('~(Form|Page)$~', '', \array_pop($classParts));

            // check if this an *Edit page and use the add-template instead
            if (\substr($className, -4) == 'Edit') {
                $className = \substr($className, 0, -4) . 'Add';
            }

            $this->templateName = \lcfirst($className);

            // assign guessed template name
            WCF::getTPL()->assign('templateName', $this->templateName);
        }
        if (empty($this->templateNameApplication)) {
            $this->templateNameApplication = \array_shift($classParts);

            // assign guessed template application
            WCF::getTPL()->assign('templateNameApplication', $this->templateNameApplication);
        }

        if ($this->useTemplate) {
            // show template
            WCF::getTPL()->display($this->templateName, $this->templateNameApplication);
        }
    }

    /**
     * Sets the active menu item of the page.
     */
    protected function setActiveMenuItem()
    {
        if (!empty($this->activeMenuItem)) {
            if (RequestHandler::getInstance()->isACPRequest()) {
                ACPMenu::getInstance()->setActiveMenuItem($this->activeMenuItem);
            }
        }
    }

    /**
     * Forces visitors to log-in themselves to access the site.
     */
    protected function forceLogin()
    {
        $allowedControllers = [
            DisclaimerPage::class,
            EmailActivationForm::class,
            EmailNewActivationCodeForm::class,
            LoginForm::class,
            LostPasswordForm::class,
            MediaPage::class,
            NewPasswordForm::class,
            RegisterActivationForm::class,
            RegisterForm::class,
            RegisterNewActivationCodeForm::class,
        ];
        if (\in_array(static::class, $allowedControllers)) {
            // controller is allowed
            return;
        }

        if (WCF::getActiveRequest()->isAvailableDuringOfflineMode()) {
            // allow access to those pages that should be always available
            return;
        }

        // force redirect to login form
        WCF::getSession()->register('__wsc_forceLoginRedirect', true);
        HeaderUtil::redirect(
            LinkHandler::getInstance()->getLink('Login', [
                'url' => WCF::getRequestURI(),
            ])
        );

        exit;
    }

    /**
     * Calls setResponse() if the parameter implements the ResponseInterface.
     *
     * @see AbstractPage::setPsr7Response()
     * @since 5.5
     */
    final protected function maybeSetPsr7Response($response): void
    {
        if ($response instanceof ResponseInterface) {
            $this->setPsr7Response($response);
        }
    }

    /**
     * Sets the PSR-7 response to return. Processing will be aborted after
     * readParameters(), readData() or show() if the response is non-null
     * and the response will be returned to the RequestHandler.
     *
     * @since 5.5
     */
    final protected function setPsr7Response(?ResponseInterface $response): void
    {
        $this->psr7Response = $response;
    }

    /**
     * Returns the current response as set using setResponse().
     *
     * @see AbstractPage::setPsr7Response()
     * @since 5.5
     */
    final protected function getPsr7Response(): ?ResponseInterface
    {
        return $this->psr7Response;
    }

    /**
     * Returns whether the current response is non-null.
     *
     * @see AbstractPage::getPsr7Response()
     * @see AbstractPage::setPsr7Response()
     * @since 5.5
     */
    final protected function hasPsr7Response(): bool
    {
        return $this->psr7Response !== null;
    }
}

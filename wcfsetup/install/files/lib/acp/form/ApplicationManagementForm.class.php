<?php

namespace wcf\acp\form;

use wcf\data\application\ViewableApplicationList;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the application management form.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
final class ApplicationManagementForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.application.management';

    /**
     * list of applications
     * @var ViewableApplicationList
     */
    public $applicationList;

    /**
     * @var string
     */
    public $cookieDomain = '';

    /**
     * @var string
     */
    public $domainName = '';

    /**
     * @var int[]
     */
    public $landingPageID = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.canManageApplication'];

    /**
     * nested list of page nodes
     * @var \RecursiveIteratorIterator
     */
    public $pageNodeList;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->pageNodeList = (new PageNodeTree())->getNodeList();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['cookieDomain'])) {
            $this->cookieDomain = StringUtil::trim($_POST['cookieDomain']);
        }
        if (isset($_POST['domainName'])) {
            $this->domainName = StringUtil::trim($_POST['domainName']);
        }
        if (isset($_POST['landingPageID']) && \is_array($_POST['landingPageID'])) {
            $this->landingPageID = ArrayUtil::toIntegerArray($_POST['landingPageID']);
        }
    }

    public function validate()
    {
        parent::validate();

        if (ApplicationHandler::getInstance()->isMultiDomainSetup()) {
            // Changes to the domain for all apps are only possible for setups using the same domain.
            if (!empty($this->cookieDomain) || !empty($this->domainName)) {
                throw new PermissionDeniedException();
            }
        }

        if (empty($this->domainName)) {
            throw new UserInputException('domainName');
        } else {
            $regex = new Regex('^https?\://');
            $this->domainName = FileUtil::removeTrailingSlash($regex->replace($this->domainName, ''));
            $this->cookieDomain = FileUtil::removeTrailingSlash($regex->replace($this->cookieDomain, ''));

            // domain may not contain path components
            $regex = new Regex('[/#\?&]');
            if ($regex->match($this->domainName)) {
                throw new UserInputException('domainName', 'containsPath');
            } elseif ($regex->match($this->cookieDomain)) {
                throw new UserInputException('cookieDomain', 'containsPath');
            }

            // strip port from cookie domain
            $regex = new Regex(':[0-9]+$');
            $this->cookieDomain = $regex->replace($this->cookieDomain, '');

            // check if cookie domain shares the same domain (may exclude subdomains)
            if (!StringUtil::endsWith($regex->replace($this->domainName, ''), $this->cookieDomain)) {
                throw new UserInputException('cookieDomain', 'invalid');
            }
        }

        foreach ($this->landingPageID as $landingPageID) {
            if (!$landingPageID) {
                continue;
            }

            $page = new Page($landingPageID);
            if (!$page->pageID) {
                throw new UserInputException('landingPageID');
            } elseif ($page->requireObjectID || $page->excludeFromLandingPage || $page->isDisabled) {
                throw new UserInputException('landingPageID', 'invalid');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->applicationList = new ViewableApplicationList();
        $this->applicationList->readObjects();

        if (!ApplicationHandler::getInstance()->isMultiDomainSetup()) {
            $core = ApplicationHandler::getInstance()->getApplicationByID(1);
            $this->domainName = $core->domainName;
            $this->cookieDomain = $core->cookieDomain;
        }
    }

    public function save()
    {
        parent::save();

        if (!ApplicationHandler::getInstance()->isMultiDomainSetup()) {
            $sql = "UPDATE  wcf" . WCF_N . "_application
                    SET     domainName = ?,
                            cookieDomain = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $this->domainName,
                $this->cookieDomain,
            ]);
        }

        $sql = "UPDATE  wcf" . WCF_N . "_application
                SET     landingPageID = ?
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        foreach ($this->landingPageID as $packageID => $landingPageID) {
            $statement->execute([
                $landingPageID ?: null,
                $packageID,
            ]);
        }

        $this->saved();

        if (!empty($this->landingPageID[1])) {
            (new Page($this->landingPageID[1]))->setAsLandingPage();
        } else {
            $sql = "UPDATE  wcf" . WCF_N . "_page
                    SET     isLandingPage = ?
                    WHERE   isLandingPage = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                0,
                1,
            ]);
        }

        ApplicationHandler::rebuild();

        // Reset caches to reflect the new landing pages.
        ApplicationCacheBuilder::getInstance()->reset();
        PageCacheBuilder::getInstance()->reset();
        RoutingCacheBuilder::getInstance()->reset();

        // Reload the applications to update the selected landing page id.
        $this->applicationList = new ViewableApplicationList();
        $this->applicationList->readObjects();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $pageList = new PageList();
        $pageList->readObjects();

        WCF::getTPL()->assign([
            'applicationList' => $this->applicationList,
            'cookieDomain' => $this->cookieDomain,
            'domainName' => $this->domainName,
            'isMultiDomainSetup' => ApplicationHandler::getInstance()->isMultiDomainSetup(),
            'pageNodeList' => $this->pageNodeList,
            'pageList' => $pageList->getObjects(),
        ]);
    }
}

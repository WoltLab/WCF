<?php

namespace wcf\acp\form;

use wcf\data\application\ApplicationList;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\cache\eager\ApplicationCache;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\style\StyleHandler;
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
 */
final class ApplicationManagementForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.application.management';

    /**
     * list of applications
     */
    public ?ApplicationList $applicationList = null;

    public string $cookieDomain = '';

    public string $domainName = '';

    /**
     * @var int[]
     */
    public array $landingPageID = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.canManageApplication'];

    /**
     * nested list of page nodes
     * @var \RecursiveIteratorIterator<PageNode>|null
     */
    public ?\RecursiveIteratorIterator $pageNodeList = null;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->pageNodeList = (new PageNodeTree())->getNodeList();
    }

    #[\Override]
    public function readFormParameters(): void
    {
        parent::readFormParameters();

        if (\ENABLE_ENTERPRISE_MODE === 0 || WCF::getUser()->hasOwnerAccess()) {
            if (isset($_POST['cookieDomain'])) {
                $this->cookieDomain = StringUtil::trim($_POST['cookieDomain']);
            }
            if (isset($_POST['domainName'])) {
                $this->domainName = StringUtil::trim($_POST['domainName']);
            }
        }

        if (isset($_POST['landingPageID']) && \is_array($_POST['landingPageID'])) {
            $this->landingPageID = ArrayUtil::toIntegerArray($_POST['landingPageID']);
        }
    }

    #[\Override]
    public function validate(): void
    {
        parent::validate();

        if (\ENABLE_ENTERPRISE_MODE === 0 || WCF::getUser()->hasOwnerAccess()) {
            if (empty($this->domainName)) {
                throw new UserInputException('domainName');
            }

            $regex = new Regex('^https?\://');
            $this->domainName = FileUtil::removeTrailingSlash($regex->replace($this->domainName, ''));
            $this->cookieDomain = FileUtil::removeTrailingSlash($regex->replace($this->cookieDomain, ''));

            // domain may not contain path components
            $regex = new Regex('[/#\?&]');
            if ($regex->match($this->domainName) !== 0) {
                throw new UserInputException('domainName', 'containsPath');
            } elseif ($regex->match($this->cookieDomain) !== 0) {
                throw new UserInputException('cookieDomain', 'containsPath');
            }

            // strip port from cookie domain
            $regex = new Regex(':[0-9]+$');
            $this->cookieDomain = $regex->replace($this->cookieDomain, '');

            // check if cookie domain shares the same domain (may exclude subdomains)
            if (!\str_ends_with($regex->replace($this->domainName, ''), $this->cookieDomain)) {
                throw new UserInputException('cookieDomain', 'invalid');
            }
        }

        foreach ($this->landingPageID as $landingPageID) {
            if ($landingPageID === 0) {
                continue;
            }

            $page = new Page($landingPageID);
            if ($page->isNil()) {
                throw new UserInputException('landingPageID');
            } elseif ($page->requireObjectID !== 0 || $page->excludeFromLandingPage !== 0 || $page->isDisabled !== 0) {
                throw new UserInputException('landingPageID', 'invalid');
            }
        }
    }

    #[\Override]
    public function readData(): void
    {
        parent::readData();

        $this->applicationList = new ApplicationList();
        $this->applicationList->readObjects();

        $core = ApplicationHandler::getInstance()->getApplicationByID(1);
        $this->domainName = $core->domainName;
        $this->cookieDomain = $core->cookieDomain;
    }

    #[\Override]
    public function save(): void
    {
        parent::save();

        if (\ENABLE_ENTERPRISE_MODE === 0 || WCF::getUser()->hasOwnerAccess()) {
            $sql = "UPDATE  wcf1_application
                    SET     domainName = ?,
                            cookieDomain = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->domainName,
                $this->cookieDomain,
            ]);
        }

        $sql = "UPDATE  wcf1_application
                SET     landingPageID = ?
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($this->landingPageID as $packageID => $landingPageID) {
            $statement->execute([
                $landingPageID ?: null,
                $packageID,
            ]);
        }

        $this->saved();

        ApplicationHandler::rebuild();

        // Reset caches to reflect the new landing pages.
        (new ApplicationCache())->rebuild();
        PageCacheBuilder::getInstance()->reset();
        RoutingCacheBuilder::getInstance()->reset();
        StyleHandler::resetStylesheets();

        // Reload the applications to update the selected landing page id.
        $this->applicationList = new ApplicationList();
        $this->applicationList->readObjects();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        $pageList = new PageList();
        $pageList->readObjects();

        WCF::getTPL()->assign([
            'applicationList' => $this->applicationList,
            'cookieDomain' => $this->cookieDomain,
            'domainName' => $this->domainName,
            'pageNodeList' => $this->pageNodeList,
            'pageList' => $pageList->getObjects(),
        ]);
    }
}

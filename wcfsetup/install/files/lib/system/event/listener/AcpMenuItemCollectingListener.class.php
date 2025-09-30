<?php

namespace wcf\system\event\listener;

use wcf\data\option\category\OptionCategoryList;
use wcf\event\acp\menu\item\ItemCollecting;
use wcf\system\cache\eager\TopOptionCategoryCache;
use wcf\system\menu\acp\AcpMenuItem;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;

/**
 * Adds the ACP menu entries of the core.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AcpMenuItemCollectingListener
{
    public function __invoke(ItemCollecting $event): void
    {
        $this->addTopLevelItems($event);
        $this->addOptionItems($event);
        $this->addPackageItems($event);

        $this->addContactFormItems($event);
    }

    private function addTopLevelItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.configuration',
            icon: FontAwesomeIcon::fromValues('wrench'),
        ));
    }

    private function addOptionItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.configuration.canEditOption')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.option',
            parentMenuItem: 'wcf.acp.menu.link.configuration'
        ));

        foreach ((new TopOptionCategoryCache())->getCache() as $optionCategory) {
            if (!$optionCategory->validateOptions() || $optionCategory->validatePermissions()) {
                continue;
            }

            $event->register(
                new AcpMenuItem(
                    'wcf.acp.option.category.' . $optionCategory->categoryName,
                    parentMenuItem: 'wcf.acp.menu.link.option',
                    link: LinkHandler::getInstance()->getControllerLink(
                        \wcf\acp\form\OptionForm::class,
                        ['id' => $optionCategory->categoryID]
                    ),
                )
            );
        }
    }

    private function addPackageItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.package',
            parentMenuItem: 'wcf.acp.menu.link.configuration'
        ));

        if (WCF::getSession()->getPermission('admin.configuration.canManageApplication')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.application.management',
                parentMenuItem: 'wcf.acp.menu.link.package',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ApplicationManagementForm::class)
            ));
        }

        if (
            WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')
            || WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage')
        ) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.package.list',
                parentMenuItem: 'wcf.acp.menu.link.package',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PackageListPage::class)
            ));
        }

        if (WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.package.license',
                parentMenuItem: 'wcf.acp.menu.link.package.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\LicensePage::class),
                icon: FontAwesomeIcon::fromValues('cart-arrow-down')
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.package.install',
                parentMenuItem: 'wcf.acp.menu.link.package.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\PackageStartInstallForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }

        if (WCF::getSession()->getPermission('admin.configuration.package.canEditServer')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.package.server.list',
                parentMenuItem: 'wcf.acp.menu.link.package',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PackageUpdateServerListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.package.server.add',
                parentMenuItem: 'wcf.acp.menu.link.package.server.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\PackageUpdateServerAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }
    }

    private function addContactFormItems(ItemCollecting $event): void
    {
        if (!\MODULE_CONTACT_FORM) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.contact.canManageContactForm')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.contact',
            parentMenuItem: 'wcf.acp.menu.link.configuration'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.contact.options',
            parentMenuItem: 'wcf.acp.menu.link.contact',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ContactOptionListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.contact.options.add',
            WCF::getLanguage()->get('wcf.acp.contact.option.add'),
            'wcf.acp.menu.link.contact.options',
            LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ContactOptionAddForm::class),
            FontAwesomeIcon::fromValues('plus'),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.contact.recipients',
            parentMenuItem: 'wcf.acp.menu.link.contact',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ContactRecipientListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.contact.recipients.add',
            WCF::getLanguage()->get('wcf.acp.contact.recipient.add'),
            'wcf.acp.menu.link.contact.recipients',
            LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ContactRecipientAddForm::class),
            FontAwesomeIcon::fromValues('plus'),
        ));
    }
}

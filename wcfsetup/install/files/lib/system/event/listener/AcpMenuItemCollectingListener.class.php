<?php

namespace wcf\system\event\listener;

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
        $this->addOtherItems($event);
        $this->addDevtoolsItems($event);
        $this->addContactFormItems($event);

        $this->addUserItems($event);
        $this->addUserGroupItems($event);
        $this->addUserRankItems($event);
        $this->addUserOptionItems($event);
        $this->addUserNoticeItems($event);
        $this->addPaidSubscriptionItems($event);
        $this->addTrophyItems($event);
    }

    private function addTopLevelItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.configuration',
            icon: FontAwesomeIcon::fromValues('wrench'),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user',
            icon: FontAwesomeIcon::fromValues('users'),
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
            'wcf.acp.menu.link.other',
            parentMenuItem: 'wcf.acp.menu.link.configuration'
        ));

        if (WCF::getSession()->getPermission('admin.user.canEditActivityPoints')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.activityPoint',
                parentMenuItem: 'wcf.acp.menu.link.other',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserActivityPointOptionForm::class)
            ));
        }

        if (WCF::getSession()->getPermission('admin.user.canManageNotificationSettings')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.notificationPresetSettings',
                parentMenuItem: 'wcf.acp.menu.link.other',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\NotificationPresetSettingsForm::class)
            ));
        }

        if (WCF::getSession()->getPermission('admin.captcha.canManageCaptchaQuestion')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.captcha.question.list',
                parentMenuItem: 'wcf.acp.menu.link.other',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\CaptchaQuestionListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.captcha.question.add',
                parentMenuItem: 'wcf.acp.menu.link.captcha.question.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\CaptchaQuestionAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }
    }

    private function addOtherItems(ItemCollecting $event): void
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

    private function addDevtoolsItems(ItemCollecting $event): void
    {
        if (!\ENABLE_DEVELOPER_TOOLS) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.devtools',
            parentMenuItem: 'wcf.acp.menu.link.configuration'
        ));

        if (!WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.devtools.project.list',
            parentMenuItem: 'wcf.acp.menu.link.devtools',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\DevtoolsProjectListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.devtools.project.add',
            parentMenuItem: 'wcf.acp.menu.link.devtools.project.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\DevtoolsProjectAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.devtools.missingLanguageItem.list',
            parentMenuItem: 'wcf.acp.menu.link.devtools',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\DevtoolsMissingLanguageItemListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.devtools.notificationTest',
            parentMenuItem: 'wcf.acp.menu.link.devtools',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\DevtoolsNotificationTestPage::class),
        ));
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

    private function addUserItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.management',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));

        if (WCF::getSession()->getPermission('admin.user.canSearchUser')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.user.list',
                parentMenuItem: 'wcf.acp.menu.link.user.management',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.user.search',
                parentMenuItem: 'wcf.acp.menu.link.user.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserSearchForm::class),
                icon: FontAwesomeIcon::fromValues('magnifying-glass')
            ));
            if (WCF::getSession()->getPermission('admin.user.canAddUser')) {
                $event->register(new AcpMenuItem(
                    'wcf.acp.menu.link.user.add',
                    parentMenuItem: 'wcf.acp.menu.link.user.list',
                    link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserAddForm::class),
                    icon: FontAwesomeIcon::fromValues('plus')
                ));
            }
        }

        if (
            WCF::getSession()->getPermission('admin.user.canEditUser')
            || WCF::getSession()->getPermission('admin.user.canDeleteUser')
            || WCF::getSession()->getPermission('admin.user.canMailUser')
        ) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.user.bulkProcessing',
                parentMenuItem: 'wcf.acp.menu.link.user.management',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserBulkProcessingForm::class)
            ));
        }
        if (WCF::getSession()->getPermission('admin.user.canMailUser')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.user.mail',
                parentMenuItem: 'wcf.acp.menu.link.user.management',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserMailForm::class)
            ));
        }
        if (WCF::getSession()->getPermission('admin.user.canManageUserOption')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.user.profileMenu',
                parentMenuItem: 'wcf.acp.menu.link.user.management',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserProfileMenuPage::class)
            ));
        }
    }

    private function addUserGroupItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.group',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));

        if (
            WCF::getSession()->getPermission('admin.user.canEditGroup')
            || WCF::getSession()->getPermission('admin.user.canDeleteGroup')
        ) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.group.list',
                parentMenuItem: 'wcf.acp.menu.link.group',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserGroupListPage::class)
            ));

            if (WCF::getSession()->getPermission('admin.user.canAddGroup')) {
                $event->register(new AcpMenuItem(
                    'wcf.acp.menu.link.group.add',
                    parentMenuItem: 'wcf.acp.menu.link.group.list',
                    link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserGroupAddForm::class),
                    icon: FontAwesomeIcon::fromValues('plus')
                ));
            }
        }

        if (WCF::getSession()->getPermission('admin.user.canMailUser')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.group.mail',
                parentMenuItem: 'wcf.acp.menu.link.group',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserMailForm::class),
            ));
        }

        if (WCF::getSession()->getPermission('admin.user.canManageGroupAssignment')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.group.assignment',
                parentMenuItem: 'wcf.acp.menu.link.group',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserGroupAssignmentListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.group.assignment.add',
                parentMenuItem: 'wcf.acp.menu.link.group.assignment',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserGroupAssignmentAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }
    }

    private function addUserRankItems(ItemCollecting $event): void
    {
        if (!\MODULE_USER_RANK) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.user.rank.canManageRank')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.rank',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.rank.list',
            parentMenuItem: 'wcf.acp.menu.link.user.rank',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserRankListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.rank.add',
            parentMenuItem: 'wcf.acp.menu.link.user.rank.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserRankAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addUserOptionItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.user.canManageUserOption')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.option',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.option.list',
            parentMenuItem: 'wcf.acp.menu.link.user.option',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserOptionListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.option.add',
            parentMenuItem: 'wcf.acp.menu.link.user.option.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserOptionAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.option.category.list',
            parentMenuItem: 'wcf.acp.menu.link.user.option',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserOptionCategoryListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.user.option.category.add',
            parentMenuItem: 'wcf.acp.menu.link.user.option.category.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserOptionCategoryAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.userOptionDefaults',
            parentMenuItem: 'wcf.acp.menu.link.user.option',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserOptionSetDefaultsForm::class),
        ));
    }

    private function addUserNoticeItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.notice.canManageNotice')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.notice',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.notice.list',
            parentMenuItem: 'wcf.acp.menu.link.notice',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\NoticeListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.notice.add',
            parentMenuItem: 'wcf.acp.menu.link.notice.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\NoticeAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addPaidSubscriptionItems(ItemCollecting $event): void
    {
        if (!\MODULE_PAID_SUBSCRIPTION) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.paidSubscription.canManageSubscription')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.paidSubscription',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.paidSubscription.list',
            parentMenuItem: 'wcf.acp.menu.link.paidSubscription',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PaidSubscriptionListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.paidSubscription.add',
            parentMenuItem: 'wcf.acp.menu.link.paidSubscription.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\PaidSubscriptionAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.paidSubscription.user.list',
            parentMenuItem: 'wcf.acp.menu.link.paidSubscription',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PaidSubscriptionUserListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.paidSubscription.transactionLog.list',
            parentMenuItem: 'wcf.acp.menu.link.paidSubscription',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PaidSubscriptionTransactionLogListPage::class),
        ));
    }

    private function addTrophyItems(ItemCollecting $event): void
    {
        if (!\MODULE_TROPHY) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.trophy.canManageTrophy')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.trophy',
            parentMenuItem: 'wcf.acp.menu.link.user'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.trophy.category.list',
            parentMenuItem: 'wcf.acp.menu.link.trophy',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\TrophyCategoryListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.trophy.category.add',
            parentMenuItem: 'wcf.acp.menu.link.trophy.category.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\TrophyCategoryAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.trophy.list',
            parentMenuItem: 'wcf.acp.menu.link.trophy',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\TrophyListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.trophy.add',
            parentMenuItem: 'wcf.acp.menu.link.trophy.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\TrophyAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.userTrophy.list',
            parentMenuItem: 'wcf.acp.menu.link.trophy',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserTrophyListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.userTrophy.add',
            parentMenuItem: 'wcf.acp.menu.link.userTrophy.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\UserTrophyAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }
}

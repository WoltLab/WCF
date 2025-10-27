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
        $this->addContactFormItems($event);
        $this->addDevtoolsItems($event);

        $this->addUserItems($event);
        $this->addUserGroupItems($event);
        $this->addUserRankItems($event);
        $this->addUserOptionItems($event);
        $this->addUserNoticeItems($event);
        $this->addPaidSubscriptionItems($event);
        $this->addTrophyItems($event);

        $this->addCmsItems($event);
        $this->addMediaItems($event);
        $this->addArticleItems($event);
        $this->addLabelItems($event);
        $this->addBbcodeItems($event);
        $this->addTagItems($event);
        $this->addAttachmentItems($event);

        $this->addStyleItems($event);
        $this->addTemplateItems($event);
        $this->addLanguageItems($event);
        $this->addSmileyItems($event);
        $this->addAdItems($event);
        $this->addReactionItems($event);

        $this->addMaintenanceItems($event);
        $this->addStatItems($event);
        $this->addLogItems($event);
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
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.content',
            icon: FontAwesomeIcon::fromValues('file-lines', true),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.application',
            icon: FontAwesomeIcon::fromValues('comments', true),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.customization',
            icon: FontAwesomeIcon::fromValues('desktop'),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.management',
            icon: FontAwesomeIcon::fromValues('gear'),
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
            if (!$optionCategory->validateOptions() || !$optionCategory->validatePermissions()) {
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

    private function addOtherItems(ItemCollecting $event): void
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
                link: LinkHandler::getInstance()->getControllerLink(
                    \wcf\acp\form\UserMailForm::class,
                    ['action' => 'all']
                )
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
                link: LinkHandler::getInstance()->getControllerLink(
                    \wcf\acp\form\UserMailForm::class,
                    ['action' => 'group']
                ),
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

    private function addCmsItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.cms',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));

        if (WCF::getSession()->getPermission('admin.content.cms.canManagePage')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.page.list',
                parentMenuItem: 'wcf.acp.menu.link.cms',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\PageListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.page.add',
                parentMenuItem: 'wcf.acp.menu.link.cms.page.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\PageAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }

        if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.menu.list',
                parentMenuItem: 'wcf.acp.menu.link.cms',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\MenuListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.menu.add',
                parentMenuItem: 'wcf.acp.menu.link.cms.menu.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\MenuAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }

        if (WCF::getSession()->getPermission('admin.content.cms.canManageBox')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.box.list',
                parentMenuItem: 'wcf.acp.menu.link.cms',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\BoxListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cms.box.add',
                parentMenuItem: 'wcf.acp.menu.link.cms.box.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\BoxAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }
    }

    private function addMediaItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.content.cms.canManageMedia')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.media',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.media.list',
            parentMenuItem: 'wcf.acp.menu.link.media',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\MediaListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.media.category.list',
            parentMenuItem: 'wcf.acp.menu.link.media',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\MediaCategoryListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.media.category.add',
            parentMenuItem: 'wcf.acp.menu.link.media.category.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\MediaCategoryAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addArticleItems(ItemCollecting $event): void
    {
        if (!\MODULE_ARTICLE) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.article',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));

        if (
            WCF::getSession()->getPermission('admin.content.article.canManageArticle')
            || WCF::getSession()->getPermission('admin.content.article.canManageOwnArticles')
            || WCF::getSession()->getPermission('admin.content.article.canContributeArticle')
        ) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.article.list',
                parentMenuItem: 'wcf.acp.menu.link.article',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ArticleListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.article.add',
                parentMenuItem: 'wcf.acp.menu.link.article.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ArticleAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }

        if (WCF::getSession()->getPermission('admin.content.article.canManageCategory')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.article.category.list',
                parentMenuItem: 'wcf.acp.menu.link.article',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ArticleCategoryListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.article.category.add',
                parentMenuItem: 'wcf.acp.menu.link.article.category.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ArticleCategoryAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }
    }

    private function addLabelItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.content.label.canManageLabel')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.label',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.label.list',
            parentMenuItem: 'wcf.acp.menu.link.label',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\LabelListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.label.add',
            parentMenuItem: 'wcf.acp.menu.link.label.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\LabelAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.label.group.list',
            parentMenuItem: 'wcf.acp.menu.link.label',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\LabelGroupListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.label.group.add',
            parentMenuItem: 'wcf.acp.menu.link.label.group.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\LabelGroupAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addBbcodeItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.content.bbcode.canManageBBCode')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.bbcode',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.bbcode.list',
            parentMenuItem: 'wcf.acp.menu.link.bbcode',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\BBCodeListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.bbcode.add',
            parentMenuItem: 'wcf.acp.menu.link.bbcode.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\BBCodeAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.bbcode.mediaProvider.list',
            parentMenuItem: 'wcf.acp.menu.link.bbcode',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\BBCodeMediaProviderListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.bbcode.mediaProvider.add',
            parentMenuItem: 'wcf.acp.menu.link.bbcode.mediaProvider.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\BBCodeMediaProviderAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addTagItems(ItemCollecting $event): void
    {
        if (!\MODULE_TAGGING) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.content.tag.canManageTag')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.tag',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.tag.list',
            parentMenuItem: 'wcf.acp.menu.link.tag',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\TagListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.tag.add',
            parentMenuItem: 'wcf.acp.menu.link.tag.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\TagAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addAttachmentItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.attachment.canManageAttachment')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.attachment',
            parentMenuItem: 'wcf.acp.menu.link.content'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.attachment.list',
            parentMenuItem: 'wcf.acp.menu.link.attachment',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\AttachmentListPage::class),
        ));
    }

    private function addStyleItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.style',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.style.list',
            parentMenuItem: 'wcf.acp.menu.link.style',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\StyleListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.style.import',
            parentMenuItem: 'wcf.acp.menu.link.style.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\StyleImportForm::class),
            icon: FontAwesomeIcon::fromValues('upload')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.style.add',
            parentMenuItem: 'wcf.acp.menu.link.style.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\StyleAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.style.globalValues',
            parentMenuItem: 'wcf.acp.menu.link.style',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\StyleGlobalValuesForm::class),
        ));
    }

    private function addTemplateItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.template.canManageTemplate')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.template',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.template.list',
            parentMenuItem: 'wcf.acp.menu.link.template',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\TemplateListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.template.add',
            parentMenuItem: 'wcf.acp.menu.link.template.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\TemplateAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.template.group.list',
            parentMenuItem: 'wcf.acp.menu.link.template',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\TemplateGroupListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.template.group.add',
            parentMenuItem: 'wcf.acp.menu.link.template.group.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\TemplateGroupAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addLanguageItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language.list',
            parentMenuItem: 'wcf.acp.menu.link.language',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\LanguageListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language.import',
            parentMenuItem: 'wcf.acp.menu.link.language.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\LanguageImportForm::class),
            icon: FontAwesomeIcon::fromValues('upload')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language.item.list',
            parentMenuItem: 'wcf.acp.menu.link.language',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\LanguageItemListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language.item.add',
            parentMenuItem: 'wcf.acp.menu.link.language.item.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\LanguageItemAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.language.multilingualism',
            parentMenuItem: 'wcf.acp.menu.link.language',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\LanguageMultilingualismForm::class),
        ));
    }

    private function addSmileyItems(ItemCollecting $event): void
    {
        if (!\MODULE_SMILEY) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.smiley',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.smiley.list',
            parentMenuItem: 'wcf.acp.menu.link.smiley',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\SmileyListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.smiley.add',
            parentMenuItem: 'wcf.acp.menu.link.smiley.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\SmileyAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.smiley.category.list',
            title: WCF::getLanguage()->get('wcf.category.list'),
            parentMenuItem: 'wcf.acp.menu.link.smiley',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\SmileyCategoryListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.smiley.category.add',
            title: WCF::getLanguage()->get('wcf.category.add'),
            parentMenuItem: 'wcf.acp.menu.link.smiley.category.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\SmileyCategoryAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addAdItems(ItemCollecting $event): void
    {
        if (!\MODULE_WCF_AD) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.ad.canManageAd')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.ad',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.ad.list',
            parentMenuItem: 'wcf.acp.menu.link.ad',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\AdListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.ad.add',
            parentMenuItem: 'wcf.acp.menu.link.ad.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\AdAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addReactionItems(ItemCollecting $event): void
    {
        if (!\MODULE_LIKE) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.content.reaction.canManageReactionType')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.reactionType',
            parentMenuItem: 'wcf.acp.menu.link.customization'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.reactionType.list',
            parentMenuItem: 'wcf.acp.menu.link.reactionType',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ReactionTypeListPage::class),
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.reactionType.add',
            parentMenuItem: 'wcf.acp.menu.link.reactionType.list',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ReactionTypeAddForm::class),
            icon: FontAwesomeIcon::fromValues('plus')
        ));
    }

    private function addMaintenanceItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.maintenance',
            parentMenuItem: 'wcf.acp.menu.link.management'
        ));

        if (WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.systemCheck',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\SystemCheckPage::class),
            ));
        }

        if (WCF::getSession()->getPermission('admin.management.canManageCronjob')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cronjob.list',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\CronjobListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.cronjob.add',
                parentMenuItem: 'wcf.acp.menu.link.cronjob.list',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\CronjobAddForm::class),
                icon: FontAwesomeIcon::fromValues('plus')
            ));
        }

        if (WCF::getSession()->getPermission('admin.management.canRebuildData')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.maintenance.cache',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\CacheListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.maintenance.rebuildData',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\RebuildDataPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.maintenance.sitemap',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\SitemapListPage::class),
            ));
        }

        if (WCF::getSession()->getPermission('admin.management.canImportData')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.maintenance.import',
                parentMenuItem: 'wcf.acp.menu.link.maintenance',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\DataImportForm::class),
            ));
        }
    }

    private function addStatItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.management.canViewLog')) {
            return;
        }

        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.stat',
            parentMenuItem: 'wcf.acp.menu.link.management'
        ));
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.stat.list',
            parentMenuItem: 'wcf.acp.menu.link.stat',
            link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\StatPage::class),
        ));
    }

    private function addLogItems(ItemCollecting $event): void
    {
        $event->register(new AcpMenuItem(
            'wcf.acp.menu.link.log',
            parentMenuItem: 'wcf.acp.menu.link.management'
        ));

        if (WCF::getSession()->getPermission('admin.management.canViewLog')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.log.session',
                parentMenuItem: 'wcf.acp.menu.link.log',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ACPSessionLogListPage::class),
            ));
        }

        if (WCF::getSession()->getPermission('admin.management.canManageCronjob')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.log.cronjob',
                parentMenuItem: 'wcf.acp.menu.link.log',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\CronjobLogListPage::class),
            ));
        }

        if (WCF::getSession()->getPermission('admin.management.canViewLog')) {
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.log.email',
                parentMenuItem: 'wcf.acp.menu.link.log',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\EmailLogListPage::class),
            ));
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.log.exception',
                parentMenuItem: 'wcf.acp.menu.link.log',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ExceptionLogViewPage::class),
            ));
            if (\ENABLE_USER_AUTHENTICATION_FAILURE) {
                $event->register(new AcpMenuItem(
                    'wcf.acp.menu.link.log.authentication.failure',
                    parentMenuItem: 'wcf.acp.menu.link.log',
                    link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\UserAuthenticationFailureListPage::class),
                ));
            }
            $event->register(new AcpMenuItem(
                'wcf.acp.menu.link.log.modification',
                parentMenuItem: 'wcf.acp.menu.link.log',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ModificationLogListPage::class),
            ));
        }
    }
}

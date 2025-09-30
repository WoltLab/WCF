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

        $this->addContactFormItems($event);
    }

    private function addTopLevelItems(ItemCollecting $event): void
    {
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.configuration',
                icon: FontAwesomeIcon::fromValues('wrench'),
            )
        );
    }

    private function addOptionItems(ItemCollecting $event): void
    {
        if (!WCF::getSession()->getPermission('admin.configuration.canEditOption')) {
            return;
        }

        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.option',
                parentMenuItem: 'wcf.acp.menu.link.configuration'
            )
        );

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

    private function addContactFormItems(ItemCollecting $event): void
    {
        if (!\MODULE_CONTACT_FORM) {
            return;
        }

        if (!WCF::getSession()->getPermission('admin.contact.canManageContactForm')) {
            return;
        }

        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.contact',
                parentMenuItem: 'wcf.acp.menu.link.configuration'
            )
        );
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.contact.options',
                parentMenuItem: 'wcf.acp.menu.link.contact',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ContactOptionListPage::class),
            )
        );
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.contact.options.add',
                WCF::getLanguage()->get('wcf.acp.contact.option.add'),
                'wcf.acp.menu.link.contact.options',
                LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ContactOptionAddForm::class),
                FontAwesomeIcon::fromValues('plus'),
            )
        );
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.contact.recipients',
                parentMenuItem: 'wcf.acp.menu.link.contact',
                link: LinkHandler::getInstance()->getControllerLink(\wcf\acp\page\ContactRecipientListPage::class),
            )
        );
        $event->register(
            new AcpMenuItem(
                'wcf.acp.menu.link.contact.recipients.add',
                WCF::getLanguage()->get('wcf.acp.contact.recipient.add'),
                'wcf.acp.menu.link.contact.recipients',
                LinkHandler::getInstance()->getControllerLink(\wcf\acp\form\ContactRecipientAddForm::class),
                FontAwesomeIcon::fromValues('plus'),
            )
        );
    }
}

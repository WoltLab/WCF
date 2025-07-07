<?php

namespace wcf\system\interaction\admin;

use wcf\acp\page\PageBoxOrderPage;
use wcf\data\page\Page;
use wcf\event\interaction\admin\PageInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\LinkableObjectInteraction;
use wcf\system\interaction\LinkInteraction;

/**
 * Interaction provider for pages.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PageInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new LinkableObjectInteraction(
                'view',
                'wcf.acp.page.button.viewPage',
                static fn(Page $page) => !$page->requireObjectID
            ),
            new LinkInteraction("order-boxes", PageBoxOrderPage::class, "wcf.acp.page.button.boxOrder"),
            new DeleteInteraction('core/pages/%s', static fn(Page $page) => $page->canDelete()),
        ]);

        EventHandler::getInstance()->fire(
            new PageInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Page::class;
    }
}

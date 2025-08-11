<?php

namespace wcf\command\page;

use wcf\data\page\Page;
use wcf\data\page\PageEditor;
use wcf\event\page\PageDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables the given page.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisablePage
{
    public function __construct(private readonly Page $page) {}

    public function __invoke(): void
    {
        if ($this->page->isDisabled) {
            return;
        }

        (new PageEditor($this->page))->update([
            'isDisabled' => 1,
        ]);

        $event = new PageDisabled($this->page);
        EventHandler::getInstance()->fire($event);
    }
}

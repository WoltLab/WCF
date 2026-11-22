<?php

namespace wcf\system\interaction\user;

use wcf\action\PageNoticeEditAction;
use wcf\data\page\notice\PageNotice;
use wcf\event\interaction\user\PageNoticeInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\request\LinkHandler;

/**
 * Interaction provider for page notices.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new FormBuilderDialogInteraction(
                'edit',
                LinkHandler::getInstance()->getControllerLink(PageNoticeEditAction::class, ['id' => '%s']),
                'wcf.global.button.edit',
                static fn(PageNotice $pageNotice): bool => $pageNotice->canEdit()
            ),
            new DeleteInteraction(
                'core/page-notices/%s',
                static fn(PageNotice $pageNotice): bool => $pageNotice->canEdit()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new PageNoticeInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return PageNotice::class;
    }
}

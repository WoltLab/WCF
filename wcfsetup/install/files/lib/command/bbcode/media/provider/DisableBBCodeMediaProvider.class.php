<?php

namespace wcf\command\bbcode\media\provider;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\event\bbcode\media\provider\BBCodeMediaProviderDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a bbcode media provider.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableBBCodeMediaProvider
{
    public function __construct(private readonly BBCodeMediaProvider $mediaProvider) {}

    public function __invoke(): void
    {
        if ($this->mediaProvider->isDisabled) {
            return;
        }

        (new BBCodeMediaProviderEditor($this->mediaProvider))->update([
            'isDisabled' => 1,
        ]);

        $event = new BBCodeMediaProviderDisabled($this->mediaProvider);
        EventHandler::getInstance()->fire($event);
    }
}

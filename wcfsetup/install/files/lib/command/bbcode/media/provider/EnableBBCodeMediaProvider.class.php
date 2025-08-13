<?php

namespace wcf\command\bbcode\media\provider;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\event\bbcode\media\provider\BBCodeMediaProviderEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a bbcode media provider.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableBBCodeMediaProvider
{
    public function __construct(private readonly BBCodeMediaProvider $mediaProvider) {}

    public function __invoke(): void
    {
        (new BBCodeMediaProviderEditor($this->mediaProvider))->update([
            'isDisabled' => 0,
        ]);

        $event = new BBCodeMediaProviderEnabled($this->mediaProvider);
        EventHandler::getInstance()->fire($event);
    }
}

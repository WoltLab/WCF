<?php

namespace wcf\event\bbcode\media\provider;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\event\IPsr14Event;

/**
 * Indicates that a media provider has been disabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class BBCodeMediaProviderDisabled implements IPsr14Event
{
    public function __construct(public readonly BBCodeMediaProvider $mediaProvider) {}
}

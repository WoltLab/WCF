<?php

namespace wcf\system\page\event;

use wcf\data\page\Page;
use wcf\system\event\IEvent;
use wcf\system\request\Request;

/**
 * Indicates that the `PageLocationManager` could not determine the active page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class ResolveCurrentPage implements IEvent
{
    public Page|null $page = null;

    public function __construct(public readonly Request $request)
    {
    }
}
